<?php

namespace App\Modules\Inbound\Transformers\GR;

use App\Libraries\Helpers;
use League\Fractal\TransformerAbstract;

class GrShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'whs_id' => $model->whs_id,
            'cus_id' => $model->cus_id,
            'asn_hdr_id' => $model->asn_hdr_id,
            'po_hdr_id' => $model->po_hdr_id,
            'po_num' => $model->po_num,
            'ctnr_id' => $model->ctnr_id,
            'ctnr_name' => data_get($model, 'container.code'),
            'seal_number' => data_get($model, 'poHdr.seal_number'),
            'gr_hdr_id' => $model->gr_hdr_id,
            'gr_hdr_num' => $model->gr_hdr_num,
            'ref_code' => $model->ref_code,
            'seq' => $model->seq,
            'putaway_cmpl_date' => $model->putaway_cmpl_date,
            'expt_date' => $model->expt_date,
            'gr_hdr_in_note' => $model->gr_hdr_in_note,
            'gr_hdr_cus_note' => $model->gr_hdr_cus_note,
            'act_date' => $model->act_date,
            'gr_hdr_sts' => $model->gr_hdr_sts,
            'gr_hdr_sts_name' => $model->gr_hdr_sts_name,
            'not_putaway_complete' => $model->not_putaway_complete,
            'of_sku' => $model->of_sku,
            'act_ctn_ttl' => $model->act_ctn_ttl,
            'act_qty' => $model->act_qty,
            'sum_qty' => (int)$model->act_qty,
            'sum_ctn_ttl' => (int)$model->act_ctn_ttl,
            'total_m3' => Helpers::formatNumberTotalM3($model->total_m3),
            'total_weight' => Helpers::formatNumberTotalM3($model->total_weight),
            'cus_code' => $model->cus_code,
            'cus_name' => $model->cus_name,
            'putter_id' => $model->putter_id,
            'putter_name' => $model->putter_name,
            'created_by_name' => $model->created_by_name,
            'details' => $model->details,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
