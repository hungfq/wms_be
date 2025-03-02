<?php

namespace App\Modules\Inbound\Transformers\GR;

use League\Fractal\TransformerAbstract;

class GrViewTransformer extends TransformerAbstract
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
            'of_pallet' => $model->of_pallet,
            'act_ctn_ttl' => $model->act_ctn_ttl,
            'act_qty' => $model->act_qty,
            'sum_qty' => (int)$model->act_qty,
            'sum_ctn_ttl' => (int)$model->act_ctn_ttl,
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

    public function getTitleExport()
    {
        return [
            'gr_hdr_sts_name|translate' => 'Status',
            'gr_hdr_num' => 'GR #',
            'po_num' => 'ASN #',
            // 'ctnr_name'         => 'Container',
            // 'ref_code'          => 'Ref Code',
            //                    'cus_code'          => 'Customer Code',
            //                    'cus_name'          => 'Customer',
            'of_sku' => 'Total SKU',
            'act_ctn_ttl' => 'Act CTNS',
            'act_qty' => 'Act QTY',
            'expt_date|format_date' => 'Expected Date',
            'act_date|format_date' => 'Received Date',
            'putter_name' => 'Putter',
            'putaway_cmpl_date|format_date' => 'Put away Complete Date',
            'created_dt|format_datetime' => 'Created At',
            'created_by_name' => 'Created By'
        ];
    }
}
