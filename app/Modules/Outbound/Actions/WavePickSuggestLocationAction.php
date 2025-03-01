<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\OrderDtl;
use App\Entities\Warehouse;
use App\Entities\WvDtl;
use App\Entities\WvHdr;
use App\Exceptions\UserException;
use App\Libraries\Config;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\WavePickSuggestLocationDTO;
use Illuminate\Support\Facades\DB;

class WavePickSuggestLocationAction
{
    public WavePickSuggestLocationDTO $dto;
    public $wvHdr;
    public $wvDtl;
    public $cartons;
    public $pickingResults = [];
    public $data;

    /**
     * @param WavePickSuggestLocationDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->validateDataInput()
            ->getCartonsCanPick()
            ->makeAutoPickingResults()
            ->transformer();

        return $this->data;
    }

    protected function validateDataInput()
    {
        $warehouse = Warehouse::query()->find($this->dto->whs_id);
        if (!$warehouse) {
            throw new UserException(Language::translate('Warehouse not found'));
        }

        $this->wvHdr = WvHdr::query()->where([
            'whs_id' => $warehouse->whs_id,
            'id' => $this->dto->wv_hdr_id,
        ])->first();

        if (!$this->wvHdr) {
            throw new UserException(Language::translate('Wave Pick not found'));
        }

        $this->wvDtl = $this->wvHdr->wvDtls()
            ->where('id', $this->dto->wv_dtl_id)
            ->first();

        if (!$this->wvDtl) {
            throw new UserException(Language::translate('Wave Pick Detail does not exist'));
        }

        if (!in_array($this->wvDtl->wv_dtl_sts, [WvDtl::STS_NEW, WvDtl::STS_PICKING])) {
            throw new UserException(
                Language::translate('Wave Pick Detail Status must be {0} or {1}',
                    Language::translate(Config::getStatusName('WV_DTL_STATUS', WvDtl::STS_NEW)),
                    Language::translate(Config::getStatusName('WV_DTL_STATUS', WvDtl::STS_PICKING))
                )
            );
        }

        $this->wvHdr->load([
            'odrHdrs.orderDtls' => function ($q) {
                $q->where('item_id', $this->wvDtl->item_id);
                $q->where('bin_loc_id', $this->wvDtl->bin_loc_id);
                $q->where('lot', $this->wvDtl->lot);
                $q->whereIn('odr_dtl_sts', [OrderDtl::STS_NEW, OrderDtl::STS_PICKING]);

                if ($this->wvDtl->lot != Config::ANY) {
                    $q->whereRaw('picked_qty < alloc_qty');
                } else {
                    $q->whereRaw('picked_qty < piece_qty');
                }
            }
        ]);

        return $this;
    }

    protected function getCartonsCanPick()
    {
        $this->wvDtl->load([
            'cartons' => function ($q) {
                $q->with(['location', 'pallet'])
                    ->filterByWvDtl($this->wvDtl)
                    ->select([
                        'cartons.*',
                        'items.pack_size',
                        DB::raw("CASE WHEN locations.goods_type = 'RT' THEN 1 ELSE 0 END AS is_retail"),
                        'locations.priority'
                    ])
                    ->join('items', function ($q) {
                        $q->on('items.item_id', '=', 'cartons.item_id')
                            ->where('items.deleted', 0);
                    })
                    ->join('locations', function ($loc) {
                        $loc->on('locations.loc_id', '=', 'cartons.loc_id')
                            ->where('locations.deleted', 0);
                    });

                $q->orderBy('cartons.piece_remain', 'ASC');

                if ($loc_code = data_get($this->dto, 'loc_code')) {
                    $q->where('cartons.loc_code', 'like', "%{$loc_code}%");
                }

                if ($exceptLocCodes = data_get($this->dto, 'except_loc_codes')) {
                    $q->whereNotIn('cartons.loc_code', $exceptLocCodes);
                }

                if ($pltNum = data_get($this->dto, 'plt_num')) {
                    $q->whereHas('pallet', function ($q1) use ($pltNum) {
                        $q1->where('rfid', 'like', "%{$pltNum}%");
                    });
                }

                if ($exceptPltNums = data_get($this->dto, 'except_plt_nums')) {
                    $q->whereDoesntHave('pallet', function ($q1) use ($exceptPltNums) {
                        $q1->whereIn('rfid', $exceptPltNums);
                    });
                }
            }
        ]);

        $this->cartons = $this->wvDtl->cartons;

        if (!$this->cartons->count()) {
            throw new UserException(Language::translate('No data available'));
        }

        return $this;
    }

    protected function makeAutoPickingResults()
    {
        $pickingQty = data_get($this->dto, 'ttl_qty', ($this->wvDtl->piece_qty - $this->wvDtl->picked_qty));

        $pickedQty = 0;
        foreach ($this->cartons->groupBy('plt_id') as $key => $carton) {
            $inPltQty = $this->cartons->where('plt_id', $key)
                ->reduce(function ($key, $value) {
                    return $key += ($value->piece_init * $value->ctn_ttl + $value->piece_remain);
                }, 0);

            $carton = $carton->first();
            $carton->in_plt_qty = $inPltQty;
            $carton->in_loc_qty = $this->cartons->where('loc_id', $carton->loc_id)->reduce(function ($key, $value) {
                return $key += ($value->piece_init * $value->ctn_ttl + $value->piece_remain);
            }, 0);

            if ($pickedQty >= $pickingQty) {
                break;
            }

            if (($inPltQty + $pickedQty) >= $pickingQty) {
                $this->pickingResults[] = [
                    'carton' => $carton,
                    'ctn_id' => $carton->ctn_id,
                    'loc_id' => $carton->loc_id,
                    'current_qty' => (int)$inPltQty,
                    'picked_qty' => (int)$pickingQty - $pickedQty,
                    'remain_qty' => (int)$inPltQty - ($pickingQty - $pickedQty),
                ];

                $pickedQty += ($pickingQty - $pickedQty);
            } else {
                $this->pickingResults[] = [
                    'carton' => $carton,
                    'ctn_id' => $carton->ctn_id,
                    'loc_id' => $carton->loc_id,
                    'current_qty' => (int)$inPltQty,
                    'picked_qty' => (int)$inPltQty,
                    'remain_qty' => 0
                ];

                $pickedQty += $inPltQty;
            }
        }

        return $this;
    }

    public function transformer()
    {
        $this->data = collect($this->pickingResults)->map(function ($result) {
            $carton = data_get($result, 'carton');
            $pickAbleQty = (int)data_get($result, 'picked_qty');
            $remainQty = (int)data_get($result, 'remain_qty');
            $packSize = (int)data_get($carton, 'pack_size');

            return [
                'loc_id' => data_get($carton, 'location.loc_id'),
                'loc_code' => data_get($carton, 'location.loc_code'),
                'loc_sts' => data_get($carton, 'location.loc_sts'),
                'plt_id' => data_get($carton, 'pallet.plt_id'),
                'plt_num' => data_get($carton, 'pallet.rfid'),
                'in_loc_qty' => data_get($carton, 'in_loc_qty'),
                'current_qty' => data_get($carton, 'in_plt_qty'),
                'plt_qty' => data_get($carton, 'in_plt_qty'),
                'pickable_qty' => $pickAbleQty,
                'pickable_ctn' => ceil($pickAbleQty / $packSize),
                'remain_qty' => ceil($remainQty),
                'remain_ctn' => ceil($remainQty / $packSize),
            ];
        })->toArray();

        return $this;
    }
}