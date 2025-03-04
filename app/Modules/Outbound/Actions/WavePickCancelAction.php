<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\Carton;
use App\Entities\EventLog;
use App\Entities\Inventory;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Entities\Warehouse;
use App\Entities\WvDtl;
use App\Entities\WvHdr;
use App\Exceptions\UserException;
use App\Libraries\Config;
use App\Libraries\Data;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\WavePickCancelDTO;
use Illuminate\Support\Facades\DB;

class WavePickCancelAction
{
    public WavePickCancelDTO $dto;
    public $wvHdr;
    public $message;
    public $events = [];
    public $orderCancelled = [];

    /**
     * @param WavePickCancelDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        foreach ($this->dto->wv_hdr_ids as $wv_hdr_id) {
            $this->validateDataInput($wv_hdr_id)
                ->updateOrderCartonAndCarton()
                ->updateOrder()
                ->updateInventory()
                ->removeWavePick();
        }

        $this->eventTracking();
    }

    protected function validateDataInput($wv_hdr_id)
    {
        $warehouse = Warehouse::query()->find($this->dto->whs_id);
        if (!$warehouse) {
            throw new UserException(Language::translate('Warehouse not found'));
        }

        $this->wvHdr = WvHdr::with(['wvDtls'])
            ->where([
                'whs_id' => $this->dto->whs_id,
                'id' => $wv_hdr_id,
            ])
            ->first();

        if (!$this->wvHdr) {
            throw new UserException(Language::translate('Wave Pick does not exists'));
        }

        if (!$this->wvHdr->wv_hdr_sts == WvHdr::STS_CANCEL) {
            throw new UserException(Language::translate('Wave Pick has been cancelled'));
        }

        if (in_array($this->wvHdr->wv_hdr_sts, [WvHdr::STS_NEW])) {
            $this->message = "Are you sure you want to cancel this wave pick?";
        }

        if (in_array($this->wvHdr->wv_hdr_sts, [WvHdr::STS_PICKING, WvHdr::STS_PICKED])) {
            $this->message = "Are you sure the Wave Pick has not been executed at the scene?";
        }

        return $this;
    }

    protected function updateOrderCartonAndCarton()
    {
        $odrCartons = $this->wvHdr->odrCartons()
            ->whereHas('carton', function ($ctn) {
                $ctn->whereNotIn('ctn_sts', [Carton::STS_ACTIVE, Carton::STS_RECEIVING]);
            })
            ->get();

        if (!$odrCartons->count()) {
            return $this;
        }

        foreach ($odrCartons as $odrCarton) {

            if ($odrCarton->orderOutSorts->count() || $odrCarton->out_sort_qty) {

                $odrNums = $odrCarton->orderOutSorts->pluck('odrHdr.odr_num')->unique()->join(', ');

                throw new UserException(Language::translate(
                    'Orders {0} has been out sort. Please contact admin for support!',
                    $odrNums
                ));
            }

            // Update Carton
            $carton = $odrCarton->carton;
            $carton->ctn_sts = Carton::STS_ACTIVE;
            $carton->picked_date = null;
            $carton->save();

            // Remove Order Carton
            $odrCarton->delete();
        }

        return $this;
    }

    protected function updateOrder()
    {
        if (!$this->wvHdr->odrHdrs->count()) {
            throw new UserException(Language::translate('No orders found for wave pick.'));
        }

        foreach ($this->wvHdr->odrHdrs as $odrHdr) {
            if (in_array($odrHdr->odr_sts, [
                OrderHdr::STS_CANCELED,
                OrderHdr::STS_NEW,
                OrderHdr::STS_OUT_SORTED,
                OrderHdr::STS_OUT_SORTING
            ])) {
                throw new UserException(Language::translate('Order status must be: {0}',
                    sprintf('%s%s%s',
                        Config::getStatusName(OrderHdr::STATUS_KEY, OrderHdr::STS_PICKING),
                        DIRECTORY_SEPARATOR,
                        Config::getStatusName(OrderHdr::STATUS_KEY, OrderHdr::STS_PICKED)
                    )
                ));
            }

            $this->orderCancelled[] = $odrHdr->odr_num;

            $odrHdr->odr_sts = OrderHdr::STS_ALLOCATED;
            $odrHdr->wv_id = null;
            $odrHdr->save();

            $this->events[] = [
                'whs_id' => $this->dto->whs_id,
                'owner' => $odrHdr->odr_num,
                'event_code' => EventLog::ORDER_UPDATE,
                'info' => 'Wave Pick {0} has been cancelled',
                'info_params' => [
                    $this->wvHdr->wv_hdr_num
                ],
            ];

            $this->events[] = [
                'whs_id' => $this->dto->whs_id,
                'owner' => $odrHdr->odr_num,
                'event_code' => EventLog::ORDER_UPDATE,
                'info' => 'Order status has been reverted to Allocate status',
                'info_params' => [
                    $odrHdr->odr_num
                ],
            ];

            $odrHdr->orderDtls()->whereNotIn('odr_dtl_sts', [
                OrderDtl::STS_CANCEL,
                OrderDtl::STS_NEW,
                OrderDtl::STS_PENDING,
                OrderDtl::STS_OUT_SORTING,
                OrderDtl::STS_OUT_SORTED
            ])
                ->update([
                    'odr_dtl_sts' => OrderDtl::STS_NEW,
                    'picked_qty' => 0,
                ]);
        }

        return $this;
    }

    protected function updateInventory()
    {
        foreach ($this->wvHdr->wvDtls as $wvDtl) {
            if ($wvDtl->wv_dtl_sts == WvDtl::STS_NEW) {
                continue;
            }

            Inventory::where('whs_id', $this->dto->whs_id)
                ->where('cus_id', $wvDtl->cus_id)
                ->where(function ($q) use ($wvDtl) {
                    $q->where('item_id', $wvDtl->item_id)
                        ->where('lot', $wvDtl->lot)
                        ->where('bin_loc_id', $wvDtl->bin_loc_id);
                })
                ->update([
                    'picked_qty' => DB::raw("picked_qty - {$wvDtl->picked_qty}"),
                    'alloc_qty' => DB::raw("alloc_qty + {$wvDtl->picked_qty}")
                ]);
        }

        return $this;
    }

    protected function removeWavePick()
    {
        $this->wvHdr->wv_hdr_sts = WvHdr::STS_CANCEL;
        $this->wvHdr->order_cancelled = implode(', ', $this->orderCancelled);
        $this->wvHdr->save();

        $this->wvHdr->wvDtls()->update([
            'wv_dtl_sts' => WvDtl::STS_CANCEL,
            'cancelled_qty' => DB::raw('picked_qty')
        ]);

        $this->events[] = [
            'whs_id' => $this->dto->whs_id,
            'owner' => $this->wvHdr->wv_hdr_num,
            'event_code' => EventLog::WAVE_PICK_CANCEL,
            'info' => 'Wave pick has been cancelled by user {0}',
            'info_params' => [
                Data::getCurrentUser()->name
            ],
        ];

        return $this;
    }

    public function eventTracking()
    {
        foreach ($this->events as $evt) {
            EventLog::query()->create($evt);
        }

        return $this;
    }

    public function getMessageSuccess()
    {
        return $this->message;
    }
}