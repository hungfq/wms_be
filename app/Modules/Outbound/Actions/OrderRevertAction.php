<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\EventLog;
use App\Entities\Inventory;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderRevertDTO;
use Illuminate\Support\Facades\DB;

class OrderRevertAction
{
    public OrderRevertDTO $dto;
    protected $odrHdrIds = [];
    protected $odrHdrs = [];

    /**
     * handle
     *
     * @param OrderRevertDTO $dto
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
                        DB::raw('piece_qty as cancel_qty')
                    ])->where('odr_dtl_sts', '<>', OrderDtl::STS_CANCEL);
                },
                'orderDtls.item',
                'wvHdr.wvDtls'
            ])
            ->whereIn('id', $this->odrHdrIds)
            ->where('whs_id', $this->dto->whs_id)
            ->get();

        $invalidOdrHdrs = collect();

        if (!$this->odrHdrs->count()) {
            throw new UserException(Language::translate('Order not found.'));
        }

        $this->odrHdrs->each(function ($odrHdr) use ($invalidOdrHdrs) {
            if ($odrHdr->odr_sts != OrderHdr::STS_ALLOCATED) {
                $invalidOdrHdrs->add($odrHdr);
            }
        });

        if ($invalidOdrHdrs->count()) {
            throw new UserException(Language::translate('List Order: {0} has invalid. Only order(s) before picked could process revert', $invalidOdrHdrs->pluck('odr_num')->join(', ')));
        }

        $this->odrHdrs->each(function ($odrHdr) {
            $this->revert($odrHdr);

            EventLog::query()->create([
                'whs_id' => $this->dto->whs_id,
                'owner' => $odrHdr->odr_num,
                'event_code' => EventLog::ORDER_REVERT,
                'info' => "{0} reverted",
                'info_params' => [
                    $odrHdr->odr_num
                ],
            ]);
        });
    }

    protected function revert($odrHdr)
    {
        $odrHdr->orderDtls->each(function ($odrDtl) use ($odrHdr) {

            Inventory::where('whs_id', $odrDtl->whs_id)
                ->where('cus_id', $odrDtl->cus_id)
                ->where(function ($q) use ($odrDtl) {
                    $q->where('item_id', $odrDtl->item_id)
                        ->where('lot', $odrDtl->lot)
                        ->where('bin_loc_id', $odrDtl->bin_loc_id);
                })->update([
                    'alloc_qty' => DB::raw("IF((alloc_qty - {$odrDtl->cancel_qty}) < 0, 0, (alloc_qty - {$odrDtl->cancel_qty}))"),
                    'avail_qty' => DB::raw("avail_qty + {$odrDtl->cancel_qty}")
                ]);
        });

        $orderDtls = $odrHdr->orderDtls()
            ->withTrashed()
            ->get();

        $orderDtls->each(function ($orderDtl) {
            if ($orderDtl->is_user_created == 1 && $orderDtl->deleted == 1) {
                $orderDtl->restore();
            } else if ($orderDtl->is_user_created == 1 && $orderDtl->deleted == 0) {
                return;
            } else if ($orderDtl->deleted == 0) { // is_user_created must be == 0
                $orderDtl->delete();
            }
        });

        $odrHdr->update([
            'odr_sts' => OrderHdr::STS_NEW
        ]);
    }
}
