<?php

namespace App\Modules\Inbound\Actions\PO;

use App\Entities\EventLog;
use App\Entities\GrHdr;
use App\Entities\PoDtl;
use App\Entities\PoHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;

class PoCompleteAction
{
    public $events = [];

    public function handle($poHdrId)
    {
        $poHdr = PoHdr::query()
            ->find($poHdrId);
        if (!$poHdr) {
            throw new UserException(Language::translate('PO not found'));
        }
        if (data_get($poHdr, 'po_sts') != PoHdr::STS_RECEIVING) {
            throw new UserException(Language::translate('PO must be Receiving'));
        }

        if (count($poHdr->grHdrs) == 0) {
            throw new UserException(Language::translate('There are no Goods Receipt on this PO Hdr'));
        }

        $grHdrInProgress = GrHdr::query()
            ->where('po_hdr_id', '=', $poHdrId)
            ->where('gr_hdr_sts', GrHdr::STS_RECEIVING)
            ->count();
        if ($grHdrInProgress > 0) {
            throw new UserException(Language::translate('There are Goods Receipt is processing'));
        }

        $poHdr->update([
            'po_sts' => PoHdr::STS_RECEIVED,
        ]);
        $poHdr->poDtls()->update([
            'po_dtl_sts' => PoDtl::STS_RECEIVED,
        ]);

        $this->events[] = [
            'whs_id' => $poHdr->whs_id,
            'event_code' => EventLog::PO_CREATE,
            'owner' => $poHdr->po_num,
            'info' => 'Complete PO #{0}',
            'info_params' => [$poHdr->po_num],
        ];

        foreach ($this->events as $evt) {
            EventLog::query()->create($evt);
        }
    }
}
