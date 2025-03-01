<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\OdrDrop;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Exceptions\UserException;
use App\Libraries\Data;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderScheduleToShipDTO;

class OrderScheduleToShipAction
{
    const ORDER_NOT_UPDATED_SHIP_INFO = 0;
    public OrderScheduleToShipDTO $dto;
    public $user;
    public $odrHdrs;

    /**
     * handle
     *
     * @param OrderScheduleToShipDTO $dto
     * @return void
     */
    public function handle($dto)
    {
        $this->dto = $dto;
        $this->user = Data::getCurrentUser();

        $this->checkData()
            ->scheduleToShipOrder()
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
            return in_array($odrHdr->odr_sts, [OrderHdr::STS_OUT_SORTED, OrderHdr::STS_PACKED, OrderHdr::STS_STAGING]);
        });

        $notValidOdrHdrs = $filterOdrHdrs->diff($this->odrHdrs);
        if ($notValidOdrHdrs->count()) {
            throw new UserException(Language::translate('Invalid order(s): {0}', implode(',', $notValidOdrHdrs->pluck('odr_num')->toArray())));
        }

        $odrShipped = $this->odrHdrs->filter(function ($odrHdr, $key) {
            return in_array($odrHdr->odr_sts, [OrderHdr::STS_SHIPPED]);
        });

        if ($odrShipped->count()) {
            throw new UserException(Language::translate('The order {0} shipped, not scheduled for shipping', implode(', ', $odrShipped->pluck('odr_num')->toArray())));
        }

        $dropOrders = OdrDrop::query()
            ->where('whs_id', $this->dto->whs_id)
            ->whereIn('odr_hdr_id', $this->dto->odr_hdr_ids)
            ->whereNotIn('status', [OdrDrop::STS_COMPLETE, OdrDrop::STS_CANCEL])
            ->count();
        if ($dropOrders > 0) {
            throw new UserException(Language::translate(
                'Please process dropped request before schedule to ship order'
            ));
        }

        return $this;
    }

    protected function scheduleToShipOrder()
    {
        OrderDtl::whereIn('odr_id', $this->odrHdrs->pluck('id')->toArray())
            ->update([
                'odr_dtl_sts' => OrderDtl::STS_SCHEDULED_TO_SHIP
            ]);

        OrderHdr::whereIn('id', $this->odrHdrs->pluck('id')->toArray())
            ->update([
                'odr_sts' => OrderHdr::STS_SCHEDULED_TO_SHIP,
                'schedule_dt' => $this->dto->schedule_dt,
            ]);

        return $this;
    }

    protected function createEventTracking()
    {
//        foreach ($this->odrHdrs as $orderHdr) {
//            event(new EventTracking([
//                'cus_id' => $orderHdr->cus_id,
//                'event_code' => EventTracking::ORDER_SCHEDULE_TO_SHIP,
//                'owner' => $orderHdr->odr_num,
//                'transaction' => $orderHdr->cus_odr_num,
//                'info' => "{0} scheduled to ship",
//                'info_params' => [
//                    $orderHdr->odr_num
//                ],
//            ]));
//        }
    }
}
