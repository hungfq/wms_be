<?php

namespace App\Modules\Inbound\Actions\PO;

use App\Entities\EventLog;
use App\Entities\PoDtl;
use App\Entities\PoHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;

class PoCancelAction
{
    public $events = [];

    public function handle($params)
    {
        $poHdrs = PoHdr::query()
            ->whereIn('po_hdr_id', $params)
            ->get();
        $poWrongStatus = [];
        foreach ($poHdrs as $poHdr) {
            if (data_get($poHdr, 'po_sts') != PoHdr::STS_NEW) {
                $poWrongStatus[] = $poHdr;
            }
        }
        if (count($poWrongStatus) > 0) {
            throw new UserException(Language::translate('PO must be NEW'));
        }

        //update po dtl
        PoDtl::whereIn('po_hdr_id', $params)->update([
            'po_dtl_sts' => PoDtl::STS_CANCEL,
        ]);

        //update po hdr
        foreach ($poHdrs as $poHdr) {
            $poHdr->update([
                'po_sts' => PoHdr::STS_CANCEL,
            ]);

            $this->events[] = [
                'whs_id' => $poHdr->whs_id,
                'event_code' => EventLog::PO_CREATE,
                'owner' => $poHdr->po_num,
                'info' => '{0} Cancelled',
                'info_params' => [$poHdr->po_num],
            ];
        }

        foreach ($this->events as $evt) {
            EventLog::query()->create($evt);
        }
    }
}
