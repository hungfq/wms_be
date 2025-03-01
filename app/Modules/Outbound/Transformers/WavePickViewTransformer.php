<?php

namespace App\Modules\Outbound\Transformers;

use App\Libraries\Config;
use League\Fractal\TransformerAbstract;

class WavePickViewTransformer extends TransformerAbstract
{
    public function transform($wvHdr)
    {
        $orderNums = $wvHdr->order_cancelled
            ? array_merge(explode(',', $wvHdr->order_cancelled), $wvHdr->odrHdrs->pluck('odr_num')->all())
            : $wvHdr->odrHdrs->pluck('odr_num')->all();

        $pickerName = $wvHdr->pickers->map(function ($q) {
            return data_get($q, 'name');
        })
            ->unique()
            ->filter();

        $pickers = implode(", ", $pickerName->toArray());

        return [
            'wv_hdr_id' => $wvHdr->id,
            'wv_hdr_num' => $wvHdr->wv_hdr_num,
            'odr_nums' => $orderNums ? implode(', ', array_unique($orderNums)) : '',
            'wv_hdr_sts' => $wvHdr->wv_hdr_sts,
            'wv_hdr_sts_name' => Config::getStatusName('WV_HDR_STATUS', $wvHdr->wv_hdr_sts),
            'pickers' => $pickers,
            'assigned_name' => $pickerName,
            'created_at' => $wvHdr->created_at,
            'updated_at' => $wvHdr->updated_at,
            'created_by_name' => data_get($wvHdr, 'created_by_name'),
            'updated_by_name' => data_get($wvHdr, 'updated_by_name'),
            'wv_dtl_ids' => $wvHdr->wvDtls->pluck('id')
        ];
    }
}
