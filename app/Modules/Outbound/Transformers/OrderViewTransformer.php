<?php

namespace App\Modules\Outbound\Transformers;

use App\Libraries\Helpers;
use League\Fractal\TransformerAbstract;

class OrderViewTransformer extends TransformerAbstract
{
    public function transform($odrHdr)
    {
        if ($odrHdr->odr_parent_id) {
            $odrNumParent = preg_replace('/-\d{1,2}$/', '', data_get($odrHdr, 'odr_num'));
        }

        $models = $odrHdr->orderDtls->map(function ($odrDtl, $index) {
            $odrDtl['total_qty'] = Helpers::formatNumber(data_get($odrDtl, 'total_qty'));
            $odrDtl['total_ctn'] = Helpers::formatNumber(data_get($odrDtl, 'total_ctn'));

            return $odrDtl->toArray() + [
                    'no' => $index + 1
                ];
        });

        return [
            'odr_id' => $odrHdr->id,
            'sapo_id' => $odrHdr->sapo_id,
            'odr_num' => $odrHdr->odr_num,
            'wv_id' => $odrHdr->wv_id,
            'wv_hdr_num' => data_get($odrHdr, 'wvHdr.wv_hdr_num'),
            'cus_id' => $odrHdr->cus_id,
            'whs_id' => $odrHdr->whs_id,
            'cus_odr_num' => $odrHdr->cus_odr_num,
            'cus_po' => $odrHdr->cus_po,
            'ref_cod' => $odrHdr->ref_cod,
            'odr_type_id' => $odrHdr->odr_type_id,
            'odr_type_code' => data_get($odrHdr, 'odrType.code'),
            'odr_type_name' => data_get($odrHdr, 'odrType.name'),
            'odr_sts' => $odrHdr->odr_sts,
            'odr_sts_name' => $odrHdr->odr_sts_name,
            'department_id' => $odrHdr->department_id,
            'department_code' => data_get($odrHdr, 'department.code'),
            'department_name' => data_get($odrHdr, 'department.name'),

            'carrier' => $odrHdr->carrier,
            'driver_name' => $odrHdr->driver_info,
            'truck_no' => $odrHdr->truck_num,
            'container_no' => $odrHdr->container_num,
            'seal_no' => $odrHdr->seal_num,
            'container_type_id' => $odrHdr->container_type_id,
            'container_type_code' => data_get($odrHdr, 'containerType.code'),
            'container_type_name' => data_get($odrHdr, 'containerType.name'),

            'cancel_by_dt' => $odrHdr->cancel_by_dt,
            'schedule_dt' => $odrHdr->schedule_dt,

            'ship_to_name' => data_get($odrHdr, 'ship_to_name'),
            'ship_to_add' => data_get($odrHdr, 'ship_to_add'),
            'ship_to_city' => $odrHdr->ship_to_city,
            'ship_to_country' => $odrHdr->ship_to_country,
            'ship_to_country_name' => $odrHdr->ship_to_country_name,
            'ship_to_state' => $odrHdr->ship_to_state,
            'ship_to_state_name' => $odrHdr->ship_to_state_name,
            'ship_to_zip' => $odrHdr->ship_to_zip,
            'ship_to_code' => $odrHdr->code,
            'tp_id' => $odrHdr->tp_id,

            'ship_by_dt' => $odrHdr->ship_by_dt,
            'shipped_dt' => $odrHdr->shipped_dt,
            'exp_shipped_date' => $odrHdr->ship_by_dt,
            'act_shipped_date' => $odrHdr->shipped_dt,
            'sku_ttl' => $odrHdr->sku_ttl,
            'in_notes' => $odrHdr->in_notes,
            'cus_notes' => $odrHdr->cus_notes,
            'truck_num' => $odrHdr->truck_num,
            'seal_num' => $odrHdr->seal_num,
            'tracking_num' => $odrHdr->tracking_num,
            'packs' => $odrHdr->packs,
            'odr_flows' => $odrHdr->odr_flows,
            'details' => $odrHdr->details,
            'models' => $models,
            'total_qty' => Helpers::formatNumber($odrHdr->total_qty),
            'total_ctn' => Helpers::formatNumber($odrHdr->total_ctn),
            'odr_parent_id' => $odrHdr->odr_parent_id,
            'odr_parent_num' => $odrNumParent ?? null,

            'sil_no' => $odrHdr->sil_no,
            'bl_no' => $odrHdr->bl_no,
            'job_no' => $odrHdr->job_no,
            'invoice_no' => $odrHdr->invoice_no,
            'invoice_date' => $odrHdr->invoice_date,
            'zip_no' => $odrHdr->zip_no,

            'created_by' => $odrHdr->created_by,
            'created_by_name' => $odrHdr->created_by_name,
            'updated_by' => $odrHdr->updated_by,
            'updated_by_name' => $odrHdr->updated_by_name,
            'created_at' => $odrHdr->created_at,
            'updated_at' => $odrHdr->updated_at,
            'amount' => number_format($odrHdr->amount),

            'is_drop_orders' => $odrHdr->orderDrops->count() ? 1 : 0,
            'is_integrate' => data_get($odrHdr, 'is_integrate'),
        ];
    }

    public function getTitleExport()
    {
        return [
            'odr_sts_name|translate' => 'Status',
            'odr_num' => 'Order #',
            'wv_hdr_num' => 'Wave Pick #',
            'ship_to_code|format_string' => 'Ship To Code',
            'ship_to_name|format_string' => 'Ship To Name',
            'cus_notes|format_string' => 'External Remark',
            'cus_odr_num|format_string' => 'S/O No.',
            'cus_po|format_string' => 'D/O No.',
            'odr_type|translate' => 'Order Type',
            'models|format_string' => 'Models',
            'total_qty|format_number_new' => 'Total Quantity',
            'total_ctn|format_number_new' => 'Total CTNS',
            'amount' => 'Amount',
            'ship_by_dt|format_date' => 'Expected Shipped Date',
            'shipped_dt|format_date' => 'Actual Shipped Date',
            'updated_by_name' => 'Updated By',
            'updated_at|format_datetime' => 'Updated Date',
            'created_by_name' => 'Created By',
            'created_at|format_datetime' => 'Created Date',
        ];
    }
}
