<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\EventLog;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderCancelDTO;
use Carbon\Carbon;

class OrderCancelAction
{
    public OrderCancelDTO $dto;
    protected $odrHdrIds = [];
    protected $odrHdrs = [];

    /**
     * handle
     *
     * @param OrderCancelDTO $dto
     * @return void
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        if ($this->dto->odr_hdr_id) {
            $this->odrHdrIds = array_merge($this->dto->odr_hdr_ids, [$this->dto->odr_hdr_id]);
        } else {
            $this->odrHdrIds = $this->dto->odr_hdr_ids;
        }

        $this->odrHdrs = OrderHdr::query()
            ->with([
                'orderDtls' => function ($q) {
                    $q->select([
                        '*',
                    ])->where('odr_dtl_sts', '<>', OrderDtl::STS_CANCEL);
                },
            ])
            ->whereIn('id', $this->odrHdrIds)
            ->where('whs_id', $this->dto->whs_id)
            ->get();

        $invalidOdrHdrs = collect();
        $this->odrHdrs->each(function ($odrHdr) use ($invalidOdrHdrs) {
            if ($odrHdr->odr_sts != OrderHdr::STS_NEW) {
                $invalidOdrHdrs->add($odrHdr);
            }
        });

        if ($invalidOdrHdrs->count()) {
            throw new UserException(Language::translate('List Order: {0} has invalid. Only order(s) NEW can be cancel', $invalidOdrHdrs->pluck('odr_num')->join(', ')));
        }

        if (!$this->odrHdrs->count()) {
            throw new UserException(Language::translate('Order not found.'));
        }

        $this->odrHdrs->each(function ($odrHdr) {
            $odrHdr->orderDtls->each(function ($odrDtl) use ($odrHdr) {
                $odrDtl->update([
                    'cancelled_qty' => $odrDtl->cancelled_qty + $odrDtl->piece_qty,
                    'odr_dtl_sts' => OrderDtl::STS_CANCEL,
                ]);
            });

            $odrHdr->update([
                'cancel_by_dt' => Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d'),
                'odr_sts' => OrderHdr::STS_CANCELED,
            ]);

            EventLog::query()->create([
                'whs_id' => $this->dto->whs_id,
                'owner' => $odrHdr->odr_num,
                'event_code' => EventLog::ORDER_CANCEL,
                'info' => "{0} cancelled",
                'info_params' => [
                    $odrHdr->odr_num
                ],
            ]);
        });
    }
}
