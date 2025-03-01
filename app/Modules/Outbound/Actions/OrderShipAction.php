<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\Carton;
use App\Entities\Inventory;
use App\Entities\OdrDrop;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderShipDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderShipAction
{
    public OrderShipDTO $dto;
    public $odrHdrs;

    /**
     * handle
     *
     * @param OrderShipDTO $dto
     * @return void
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->shipOrder()
            ->createEventTracking();
    }

    protected function checkData()
    {
        $this->odrHdrs = OrderHdr::where('whs_id', $this->dto->whs_id)
            ->whereIn('id', $this->dto->odr_hdr_ids)->get();

        if (!$this->odrHdrs->count()) {
            throw new UserException(Language::translate('Order(s) not found'));
        }

        if ($orderInvalid = array_diff($this->dto->odr_hdr_ids, $this->odrHdrs->pluck('id')->toArray())) {
            throw new UserException(Language::translate('Order Id(s) not found: {0}', collect($orderInvalid)->join(', ')));
        }

        $filterOdrHdrs = $this->odrHdrs->filter(function ($odrHdr, $key) {
            return in_array($odrHdr->odr_sts, [OrderHdr::STS_SCHEDULED_TO_SHIP]);
        });

        $notValidOdrHdrs = $this->odrHdrs->diff($filterOdrHdrs);
        if ($notValidOdrHdrs->count()) {
            throw new UserException(Language::translate('Invalid order(s): {0}', implode(',', $notValidOdrHdrs->pluck('odr_num')->toArray())));
        }

        return $this;
    }

    protected function shipOrder()
    {
        foreach ($this->odrHdrs as $odrHdr) {
            $this->performTransaction($odrHdr->id, function () use ($odrHdr) {
                DB::transaction(function () use ($odrHdr) {
                    $this->__shipOneOrder($odrHdr);
                });
            });
        }

        return $this;
    }

    private function __shipOneOrder($odrHdr)
    {
        $existUnprocessedDrop = $odrHdr->orderDrops()
            ->whereNotIn('status', [OdrDrop::STS_COMPLETE, OdrDrop::STS_CANCEL])
            ->exists();
        if ($existUnprocessedDrop) {
            throw new UserException(Language::translate(
                'Please process dropped request before ship order'
            ));
        }

        $odrHdr->orderDtls()->update([
            'odr_dtl_sts' => OrderDtl::STS_SHIPPED
        ]);

        $odrCartons = $odrHdr->odrCartons()
            ->select([
                'id',
                'ctn_id',
                'picked_qty',
                'odr_dtl_id',
                'updated_by'
            ])
            ->get();

        $cartons = Carton::query()
            ->whereIn('ctn_id', $odrCartons->pluck('ctn_id')->toArray())
            ->where('ctn_sts', Carton::STS_OUT_SORTED)
            ->get();

        if (!$cartons->count()) {
            throw new UserException("Could not found out sorted carton(s).");
        }

        $cartons->reduce(function ($carry, $carton) use ($odrCartons, $odrHdr) {
            $carton->update([
                'ctn_sts' => Carton::STS_SHIPPED
            ]);
        });

        $odrHdr->shipped_dt = $this->dto->shipped_dt;
        $odrHdr->odr_sts = OrderHdr::STS_SHIPPED;

        $this->updateInventory($odrHdr->orderDtls->where('odr_dtl_sts', OrderDtl::STS_SHIPPED));

        $odrHdr->save();
    }

    public function updateInventory($odrDetails)
    {
        foreach ($odrDetails as $odrDetail) {
            $inventory = Inventory::where([
                'whs_id' => data_get($odrDetail, 'whs_id'),
                'cus_id' => data_get($odrDetail, 'cus_id'),
                'item_id' => data_get($odrDetail, 'item_id'),
                'lot' => data_get($odrDetail, 'lot'),
                'bin_loc_id' => data_get($odrDetail, 'bin_loc_id'),
            ])->first();

            if (!$inventory) {
                return $this;
            }

            $inventory->update([
                'picked_qty' => DB::raw("picked_qty - {$odrDetail->picked_qty}"),
                'ttl' => DB::raw("ttl - {$odrDetail->picked_qty}")
            ]);
        }
    }

    protected function createEventTracking()
    {
//        foreach ($this->odrHdrs as $orderHdr) {
//            event(new EventTracking([
//                'cus_id' => $orderHdr->cus_id,
//                'event_code' => EventTracking::ORDER_SHIP,
//                'owner' => $orderHdr->odr_num,
//                'transaction' => $orderHdr->cus_odr_num,
//                'info' => "{0} shipped",
//                'info_params' => [
//                    $orderHdr->odr_num
//                ],
//            ]));
//        }
    }

    protected function performTransaction($dataId, $callback)
    {
        $lockKey = "transaction_lock_ship_{$dataId}";

        $mutex = Cache::lock($lockKey);

        if ($mutex->get()) {
            try {
                $callback();
            } finally {
                $mutex->forceRelease();
            }
        } else {
            throw new UserException(Language::translate('This Order is being ship by another user!'));
        }
    }
}
