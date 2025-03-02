<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\EventLog;
use App\Entities\OrderHdr;
use App\Entities\Warehouse;
use App\Entities\WhsConfig;
use App\Entities\WvDtl;
use App\Entities\WvHdr;
use App\Exceptions\UserException;
use App\Libraries\Data;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\WavePickCreateDTO;
use Illuminate\Support\Str;

class WavePickCreateAction
{
    public WavePickCreateDTO $dto;
    public $odrHdrs;
    public $events = [];
    public $wvHdrs = [];

    /**
     * @param WavePickCreateDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->createWavePick()
            ->eventTracking();
    }

    protected function checkData()
    {
        $warehouse = Warehouse::find($this->dto->whs_id);
        if (!$warehouse) {
            throw new UserException(Language::translate('Warehouse not found'));
        }

        $this->odrHdrs = OrderHdr::with(['orderDtls'])
            ->where('whs_id', $this->dto->whs_id)
            ->whereIn('id', $this->dto->odr_hdr_ids)
            ->get();

        if (!$this->odrHdrs->count()) {
            throw new UserException(Language::translate('Order(s) not found!'));
        }

        $this->odrHdrs->each(function ($odrHdr) {
            if ($odrHdr->odr_sts != OrderHdr::STS_ALLOCATED) {
                throw new UserException(Language::translate('Only order "Allocated" can create wavepick!'));
            }
        });

        return $this;
    }

    protected function createWavePick()
    {
        $combineWavePick = Data::getWhsConfig(WhsConfig::CONFIG_CAN_COMBINE_WAVEPICK);

        if ($combineWavePick) {
            $this->createCombineWavePick();
        } else {
            $this->createSeparateWavePick();
        }

        return $this;
    }

    protected function createCombineWavePick()
    {
        $wvHdr = [
            'whs_id' => $this->dto->whs_id,
            'wv_hdr_num' => WvHdr::generateWvHdrNum(),
            'wv_hdr_sts' => WvHdr::STS_NEW,
        ];

        $odrDtls = $this->odrHdrs->pluck('orderDtls')->flatten();

        $odrDtlGroups = $odrDtls->groupBy(function ($odrDtl) {
            return Str::upper(sprintf('%s-%s-%s', $odrDtl->item_id, $odrDtl->lot, $odrDtl->bin_loc_id));
        });

        $wvDtls = [];

        $odrDtlGroups->each(function ($odrDtlGroup) use (&$wvDtls) {
            $pieceQty = (int)$odrDtlGroup->sum('alloc_qty');
            $firstOdrDtl = $odrDtlGroup->first();

            if ($pieceQty <= 0) {
                return;
            }

            $wvDtls[] = [
                'whs_id' => $this->dto->whs_id,
                'cus_id' => $firstOdrDtl->cus_id,
                'item_id' => $firstOdrDtl->item_id,
                'bin_loc_id' => $firstOdrDtl->bin_loc_id,
                'lot' => $firstOdrDtl->lot,
                'wv_dtl_sts' => WvDtl::STS_NEW,
                'piece_qty' => $pieceQty,
                'picked_qty' => 0
            ];
        });

        if (count($wvDtls)) {
            $wvHdr = WvHdr::create($wvHdr);

            $wvHdr->wvDtls()->createMany($wvDtls);

            $this->events[] = [
                'whs_id' => $this->dto->whs_id,
                'owner' => $wvHdr->wv_hdr_num,
                'event_code' => EventLog::WAVE_PICK_CREATED,
                'info' => '{0} has been created',
                'info_params' => [$wvHdr->wv_hdr_num],
            ];
        }

        $this->odrHdrs->each(function ($odrHdr) use ($wvHdr, &$events) {
            $odrHdr->update([
                'odr_sts' => OrderHdr::STS_PICKING,
                'wv_id' => $wvHdr->id
            ]);

            $this->events[] = [
                'whs_id' => $this->dto->whs_id,
                'owner' => $odrHdr->odr_num,
                'event_code' => EventLog::ORDER_PICKING,
                'info' => '{0} picking, {1} has been created',
                'info_params' => [$odrHdr->odr_num, $wvHdr->wv_hdr_num],
            ];
        });

        $this->wvHdrs[] = $wvHdr;

        return $this;
    }

    protected function createSeparateWavePick()
    {
        $this->odrHdrs->each(function ($odrHdr) {
            $wvHdr = [
                'whs_id' => $this->dto->whs_id,
                'wv_hdr_num' => WvHdr::generateWvHdrNum(),
                'wv_hdr_sts' => WvHdr::STS_NEW,
            ];

            $wvDtls = [];
            $odrHdr->orderDtls()->each(function ($odrDtl) use (&$wvDtls) {
                $pieceQty = (int)$odrDtl->alloc_qty;

                if ($pieceQty <= 0) {
                    return;
                }

                $wvDtls[] = [
                    'whs_id' => $this->dto->whs_id,
                    'cus_id' => $odrDtl->cus_id,
                    'item_id' => $odrDtl->item_id,
                    'bin_loc_id' => $odrDtl->bin_loc_id,
                    'lot' => $odrDtl->lot,
                    'wv_dtl_sts' => WvDtl::STS_NEW,
                    'piece_qty' => $pieceQty,
                    'picked_qty' => 0
                ];
            });

            $wvHdr = WvHdr::create($wvHdr);

            $wvHdr->wvDtls()->createMany($wvDtls);

            $this->events[] = [
                'whs_id' => $this->dto->whs_id,
                'owner' => $wvHdr->wv_hdr_num,
                'event_code' => EventLog::WAVE_PICK_CREATED,
                'info' => '{0} has been created',
                'info_params' => [$wvHdr->wv_hdr_num],
            ];

            $odrHdr->update([
                'odr_sts' => OrderHdr::STS_PICKING,
                'wv_id' => $wvHdr->id
            ]);

            $this->events[] = [
                'whs_id' => $this->dto->whs_id,
                'owner' => $odrHdr->odr_num,
                'event_code' => EventLog::ORDER_PICKING,
                'info' => '{0} picking, {1} has been created',
                'info_params' => [$odrHdr->odr_num, $wvHdr->wv_hdr_num],
            ];

            $this->wvHdrs[] = $wvHdr;
        });

        return $this;
    }

    public function eventTracking()
    {
        foreach ($this->events as $evt) {
            EventLog::query()->create($evt);
        }

        return $this;
    }
}