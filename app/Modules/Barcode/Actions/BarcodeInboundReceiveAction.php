<?php

namespace App\Modules\Barcode\Actions;

use App\Entities\Carton;
use App\Entities\Container;
use App\Entities\GrDtl;
use App\Entities\GrHdr;
use App\Entities\Location;
use App\Entities\Pallet;
use App\Entities\PoDtl;
use App\Entities\PoHdr;
use App\Exceptions\UserException;
use App\Libraries\Config;
use App\Libraries\Language;
use App\Modules\Barcode\DTO\BarcodeInboundReceiveDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BarcodeInboundReceiveAction
{
    public BarcodeInboundReceiveDTO $dto;
    public $poDtl;
    public $poHdr;
    public $pallet;
    public $whsId;
    public $grHdr;
    public $container;
    public $grDtl;
    public $ctnQty;
    public $item;
    public $events;
    public $toLocation;

    /**
     * @param BarcodeInboundReceiveDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        // receiving + put-away
        $this->validateData()
            ->validateToLocation()
            ->createOrUpdatePalletReceiving()
            ->calculateCartonQty()
            ->createOrUpdateGoodsReceiptOfReceiveCarton()
            ->createOrUpdateGrDtlOfReceive()
            ->createCartons()
            ->updatePoDtl()
            ->updatePoHdr()
            ->updatePutawayPutter();
    }

    public function validateData()
    {
        //check po dtl exist
        $this->poDtl = PoDtl::query()->find($this->dto->po_dtl_id);
        if (!$this->poDtl) {
            throw new UserException(Language::translate('Po Dtl {0} doest not exist', data_get($this->dto, 'po_dtl_id')));
        }

        if (data_get($this->poDtl, 'lot') != Config::RETAIL) {
            throw new UserException(Language::translate('Only RETAIL can be receive in retail location'));
        }

        //check status po hdr
        $this->poHdr = $this->poDtl->poHdr()->where('whs_id', $this->dto->whs_id)->first();
        if (!$this->poHdr) {
            throw new UserException(Language::translate('PO not found'));
        }

        $this->item = data_get($this->poDtl, 'item');
        if (!$this->item) {
            throw new UserException(Language::translate('Item does not existed'));
        }
        if (data_get($this->item, 'pack_size') < 1) {
            throw new UserException(Language::translate('Item invalid pack size'));
        }

        if (!in_array(data_get($this->poHdr, 'po_sts'), [PoHdr::STS_NEW, PoHdr::STS_RECEIVING])) {
            throw new UserException(Language::translate(
                'This PO {0} is completed Or Cancelled',
                data_get($this->poHdr, 'po_num')
            ));
        }

        return $this;
    }

    public function validateToLocation()
    {
        $toLocCode = $this->dto->loc_code;

        $this->toLocation = Location::where([
            'whs_id' => $this->dto->whs_id,
            'loc_code' => $toLocCode
        ])->first();

        if (!$this->toLocation) {
            throw new UserException(Language::translate('Location {0} does not exist', $toLocCode));
        }

        if ($this->toLocation->loc_sts != Location::LOCATION_STATUS_ACTIVE) {
            throw new UserException(Language::translate('To location is {0} status',
                Language::translate($this->toLocation->statuses->sts_name)
            ));
        }

        if ($this->toLocation->is_full == Location::IS_FULL_YES) {
            throw new UserException(Language::translate('Location {0} is full', $toLocCode));
        }

        if (!in_array(data_get($this->toLocation, 'goods_type'), [Location::GOODS_TYPE_WAVEPICK, Location::GOODS_TYPE_RETAIL])) {
            throw new UserException(Language::translate('Location {0} is wholesale, please use pallet', $toLocCode));
        }

        if (!$this->toLocation->zone_id) {
            throw new UserException(Language::translate('To location {0} is not belong to Zone', $toLocCode));
        }

//        $zone = data_get($this->toLocation, 'zone');
//
//        if (!$zone) {
//            throw new UserException(Language::translate("Could not found zone of Location"));
//        }
//
//        if ($zone->zone_sts != Zone::DEFAULT_STS_ACTIVE) {
//            throw new UserException(Language::translate("Zone Status must be Active"));
//        }
//
//        if ($zone->zoneType->status <> ZoneType::DEFAULT_STS_ACTIVE) {
//            throw new UserException(Language::translate("Zone Type must be Active"));
//        }
//
//        if ($zone->zoneType->zone_type_code <> ZoneType::TYPE_STORAGE) {
//            throw new UserException(Language::translate("Zone Type must be Storage"));
//        }

        $anotherBinInToLocation = Carton::query()
            ->where('whs_id', $this->dto->whs_id)
            ->where('loc_id', data_get($this->toLocation, 'loc_id'))
            ->where('bin_loc_id', '<>', data_get($this->poDtl, 'bin_loc_id'))
            ->whereIn('ctn_sts', [Carton::STS_ACTIVE, Carton::STS_RECEIVING])
            ->first();
        if ($anotherBinInToLocation) {
            throw new UserException(Language::translate(
                'Location {0} already contain bin location {1}',
                data_get($this->toLocation, 'loc_code'),
                data_get($anotherBinInToLocation, 'binLocation.code'),
            ));
        }

        if (data_get($this->toLocation, 'can_mix_sku') == 1) {
            return $this;
        } else {
            $itemOnLocationInput = Carton::query()
                ->where('loc_id', data_get($this->toLocation, 'loc_id'))
                ->whereNotIn('ctn_sts', [
                    Carton::STS_PICKED,
                    Carton::STS_OUT_SORTED,
                    Carton::STS_SHIPPED,
                    Carton::DEFAULT_STS_INACTIVE,
                ])
                ->pluck('item_id')
                ->filter()->unique()->toArray();

            $countItem = count(array_unique(array_filter(array_merge($itemOnLocationInput, [data_get($this->item, 'item_id')]))));
            if ($countItem > 1) {
                throw new UserException(Language::translate(
                    'Location {0} already sku, please choose Location mix sku or Location has another new',
                    $this->dto->loc_code
                ));
            }
        }

        return $this;
    }

    public function createOrUpdatePalletReceiving()
    {
        $this->pallet = Pallet::query()
            ->where([
                'whs_id' => $this->dto->whs_id,
                'loc_id' => data_get($this->toLocation, 'loc_id'),
            ])
            ->where('rfid', 'LIKE', 'VIR-%')
            ->first();

        if (!$this->pallet) {
            $this->pallet = $this->createNewPallet();
        }

        return $this;
    }

    public function calculateCartonQty()
    {
        $pieceQty = $this->dto->qty;
        $ctnTtl = floor($pieceQty / data_get($this->item, 'pack_size'));
        $pieceRemain = $pieceQty - ($ctnTtl * data_get($this->item, 'pack_size'));

        $this->ctnQty = (object)[
            'piece_init' => data_get($this->item, 'pack_size'),
            'ctn_ttl' => $ctnTtl,
            'piece_remain' => $pieceRemain,
            'total_qty' => $pieceQty,
            'total_ctn' => ceil($pieceQty / data_get($this->item, 'pack_size')),
        ];

        return $this;
    }

    public function createCartons()
    {
        $carton = Carton::where([
            'whs_id' => data_get($this->dto, 'whs_id'),
            'loc_id' => data_get($this->toLocation, 'loc_id'),
            'plt_id' => data_get($this->pallet, 'plt_id'),
            'item_id' => data_get($this->item, 'item_id'),
            'bin_loc_id' => data_get($this->poDtl, 'bin_loc_id'),
            'lot' => data_get($this->poDtl, 'lot'),
            'po_dtl_id' => data_get($this->poDtl, 'po_dtl_id'),
            'gr_hdr_id' => data_get($this->grHdr, 'gr_hdr_id'),
            'gr_dtl_id' => data_get($this->grDtl, 'gr_dtl_id'),
            'vendor_id' => data_get($this->poDtl, 'vendor_id'),
            'piece_init' => $this->ctnQty->piece_init,
        ])->first();

        if (!$carton) {
            $dataInsert = [
                'cus_id' => data_get($this->poHdr, 'cus_id'),
                'piece_init' => $this->ctnQty->piece_init,
                'piece_remain' => $this->ctnQty->piece_remain,
                'ctn_ttl' => $this->ctnQty->ctn_ttl,
                'ctn_sts' => Carton::STS_RECEIVING,
                'is_dmg' => 0,
                'storaged_date' => date('Y-m-d'),

                'whs_id' => data_get($this->dto, 'whs_id'),
                'loc_id' => data_get($this->toLocation, 'loc_id'),
                'loc_code' => data_get($this->toLocation, 'loc_code'),
                'loc_name' => data_get($this->toLocation, 'loc_name'),
                'plt_id' => data_get($this->pallet, 'plt_id'),
                'item_id' => data_get($this->item, 'item_id'),
                'bin_loc_id' => data_get($this->poDtl, 'bin_loc_id'),
                'lot' => data_get($this->poDtl, 'lot'),
                'po_dtl_id' => data_get($this->poDtl, 'po_dtl_id'),
                'gr_hdr_id' => data_get($this->grHdr, 'gr_hdr_id'),
                'gr_dtl_id' => data_get($this->grDtl, 'gr_dtl_id'),
                'vendor_id' => data_get($this->poDtl, 'vendor_id'),
            ];

            Carton::query()->create($dataInsert);
        } else {
            $carton->ctn_ttl += $this->ctnQty->ctn_ttl;
            $carton->piece_remain += $this->ctnQty->piece_remain;
            if ($carton->piece_remain >= $carton->piece_init) { // correct carton
                $carton->ctn_ttl += floor($carton->piece_remain / $carton->piece_init);
                $carton->piece_remain = $carton->piece_remain % $carton->piece_init;
            }

            $carton->save();
        }

        return $this;
    }

    public function updatePoDtl()
    {
        if ($this->poDtl->po_dtl_sts == PoDtl::STS_NEW) {
            $this->poDtl->po_dtl_sts = PoDtl::STS_RECEIVING;
        }

        $this->poDtl->act_qty += $this->ctnQty->total_qty;
        $this->poDtl->act_ctn_ttl = ceil($this->poDtl->act_qty / data_get($this->item, 'pack_size'));
        $this->poDtl->disc_qty = ($this->poDtl->act_qty) - $this->poDtl->exp_qty;
        $this->poDtl->disc_ctn_ttl = ($this->poDtl->act_ctn_ttl) - $this->poDtl->exp_ctn_ttl;
        $this->poDtl->save();

        return $this;
    }

    public function updatePoHdr()
    {
        if ($this->poHdr->po_sts == PoHdr::STS_NEW) {
            $this->poHdr->po_sts = PoHdr::STS_RECEIVING;
        }

        $this->poHdr->seq = $this->poHdr->grHdrs()->count();
        $this->poHdr->updated_at = Carbon::now();

        $this->poHdr->save();

        return $this;
    }

    public function createOrUpdateGoodsReceiptOfReceiveCarton()
    {
        $this->grHdr = $this->getFirstGoodsReceiptByContainer();

        if ($this->grHdr) {
            return $this;
        }

        $containerNum = sprintf('CTNR-%s', time());
        $this->container = $this->createOrGetFirstContainerByNum($containerNum);

        $this->grHdr = $this->getFirstGoodsReceiptByContainer($this->container->ctnr_id);

        if ($this->grHdr) {
            return $this;
        }

        $this->grHdr = $this->createGoodsReceiptByContainer($this->container->ctnr_id);

        $this->events['GRCR'] = [
            'info' => 'GUN - Create GR #{0} successful',
            'info_params' => [$this->grHdr->gr_hdr_num],
            'params' => [$this->grHdr]
        ];

        return $this;
    }

    public function createOrGetFirstContainerByNum($containerNum)
    {
        $container = Container::query()
            ->where('code', $containerNum)
            ->first();

        if ($container) {
            return $container;
        }

        return Container::query()->create([
            'code' => $containerNum
        ]);
    }

    public function getFirstGoodsReceiptByContainer($containerId = null)
    {
        $query = GrHdr::query()
            ->where(function ($q) {
                $q->where('po_hdr_id', data_get($this->poHdr, 'po_hdr_id'))
                    ->where('whs_id', data_get($this->poHdr, 'whs_id'));
            });

        if ($containerId) {
            $query->where('ctnr_id', $containerId);
        } else {
            $query->whereNotIn('gr_hdr_sts', [GrHdr::STS_CANCEL, GrHdr::STS_COMPLETE]);
        }

        return $query->first();
    }

    public function createGoodsReceiptByContainer($containerId)
    {
        $param = [
            'ctnr_id' => $containerId,
            'po_hdr_id' => data_get($this->poHdr, 'po_hdr_id'),
            'whs_id' => data_get($this->poHdr, 'whs_id'),
            'cus_id' => data_get($this->poHdr, 'cus_id'),
            'gr_hdr_num' => GrHdr::generateGrHdrNum($this->poHdr),
            'ref_code' => data_get($this->poHdr, 'ref_code'),
            'seq' => ++$this->poHdr->seq,
            'exp_date' => data_get($this->poHdr, 'exp_date'),
            'gr_hdr_in_note' => data_get($this->dto, 'in_note', ''),
            'gr_hdr_cus_note' => data_get($this->dto, 'cus_note', ''),
            'act_date' => Carbon::now(),
            'gr_hdr_sts' => GrHdr::STS_RECEIVING,
        ];

        return GrHdr::query()->create($param);
    }

    public function createOrUpdateGrDtlOfReceive()
    {
        if (in_array($this->grHdr->gr_hdr_sts, [GrHdr::STS_CANCEL, GrHdr::STS_COMPLETE])) {
            throw new UserException(Language::translate(
                'This Container: {0}  of PO: {1} is {2}',
                data_get($this->dto, 'ctnr_code'),
                data_get($this->poHdr, 'po_num'),
                data_get($this->grHdr, 'statuses.sts_name')
            ));
        }

        $this->grDtl = GrDtl::query()
            ->where(function ($q) {
                $q->where('gr_hdr_id', data_get($this->grHdr, 'gr_hdr_id'))
                    ->where('lot', data_get($this->poDtl, 'lot'))
                    ->where('item_id', data_get($this->poDtl, 'item.item_id'))
                    ->where('bin_loc_id', data_get($this->poDtl, 'bin_loc_id'));
            })
            ->first();

        if (!$this->grDtl) {
            $dataInsert = [
                'gr_hdr_id' => $this->grHdr->gr_hdr_id,
                'po_dtl_id' => $this->poDtl->po_dtl_id,
                'po_num' => $this->poHdr->po_num,
                'act_qty' => $this->ctnQty->total_qty,
                'act_ctn_ttl' => $this->ctnQty->total_ctn,
                'lot' => data_get($this->poDtl, 'lot'),
                'plt_ttl' => 1,
                'item_id' => $this->poDtl->item_id,
                'bin_loc_id' => $this->poDtl->bin_loc_id,
                'uom_id' => $this->item->uom_id,
                'gr_dtl_sts' => GrDtl::STS_RECEIVING
            ];

            $this->grDtl = GrDtl::query()->create($dataInsert);

            return $this;
        }

        $this->grDtl->act_qty += $this->ctnQty->total_qty;
        $this->grDtl->act_ctn_ttl = ceil($this->grDtl->act_qty / data_get($this->item, 'pack_size'));
        $this->grDtl->save();

        return $this;
    }

    public function createNewPallet()
    {
        $virPltNum = Pallet::generatePalletNum($this->dto->whs_id);
        $virPalletData = [
            'whs_id' => data_get($this->dto, 'whs_id'),
            'cus_id' => data_get($this->poHdr, 'cus_id'),
            'plt_num' => sprintf('%s-%04d', $virPltNum, 1),
            'rfid' => $virPltNum,
            'plt_sts' => Pallet::STS_ACTIVE,
            'loc_id' => data_get($this->toLocation, 'loc_id'),
            'loc_code' => data_get($this->toLocation, 'loc_code'),
            'loc_name' => data_get($this->toLocation, 'loc_name'),
            'mixed_sku' => 1,
        ];

        return Pallet::query()->create($virPalletData);
    }

    protected function updatePutawayPutter()
    {
        if (!data_get($this->grHdr, 'putter_id')) {
            $this->grHdr->update([
                'putter_id' => Auth::id(),
            ]);
        }

        return $this;
    }
}
