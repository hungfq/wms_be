<?php

namespace App\Modules\Outbound\Transformers;

use League\Fractal\TransformerAbstract;

class OrderShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'odr_id' => $model->id,
            'odr_num' => $model->odr_num,
            'cus_id' => $model->cus_id,
            'whs_id' => $model->whs_id,
            'cus_odr_num' => $model->cus_odr_num,
            'cus_po' => $model->cus_po,
            'customer_name' => data_get($model, 'customer.name'),
            'odr_type' => $model->odr_type,
            'odr_type_id' => $model->odr_type_id,
            'odr_type_code' => data_get($model, 'odrType.code'),
            'odr_type_name' => data_get($model, 'odrType.name'),
            'odr_sts' => $model->odr_sts,
            'odr_sts_name' => data_get($model, 'statuses.sts_name'),
            'department_id' => $model->department_id,
            'department_code' => data_get($model, 'department.code'),
            'department_name' => data_get($model, 'department.name'),

            'carrier' => $model->carrier,
            'driver_name' => $model->driver_info,
            'truck_no' => $model->truck_num,
            'container_no' => $model->container_num,
            'seal_no' => $model->seal_num,

            'cancel_by_dt' => $model->cancel_by_dt,
            'schedule_dt' => $model->schedule_dt,

            'tp_id' => $model->tp_id,
            'code' => $model->code,
            'vat_code' => $model->vat_code,
            'ship_to_name' => $model->ship_to_name,
            'ship_to_add' => $model->ship_to_add,
            'ship_to_city' => $model->ship_to_city,
            'ship_to_country' => $model->ship_to_country,
            'ship_to_country_name' => $model->ship_to_country_name,
            'ship_to_state' => $model->ship_to_state,
            'ship_to_state_name' => $model->ship_to_state_name,
            'zip_code' => $model->ship_to_zip,
            'phone' => $model->ship_to_phone,
            'fax' => $model->ship_to_fax,

            'ship_by_dt' => $model->ship_by_dt,
            'shipped_dt' => $model->shipped_dt,
            'sku_ttl' => $model->sku_ttl,
            'in_notes' => $model->in_notes,
            'cus_notes' => $model->cus_notes,
            'truck_num' => $model->truck_num,
            'seal_num' => $model->seal_num,
            'out_pallets' => $model->out_pallets,
            'details' => $model->details,
            'is_split_orders' => data_get($model, 'is_split_orders'),
            'is_combine_order' => data_get($model, 'is_combine_order'),
            'is_drop_order' => data_get($model, 'is_drop_order'),

            'bl_no' => $model->bl_no,
            'job_no' => $model->job_no,
            'invoice_no' => $model->invoice_no,
            'invoice_date' => $model->invoice_date,
            'zip_no' => $model->zip_no,

            'created_by' => $model->created_by,
            'updated_by' => $model->updated_by,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,

            'ttl_qty_origin' => (int)data_get($model, 'ttl_qty_split_org', 0),
            'ttl_ctn_origin' => (int)data_get($model, 'ttl_ctn_split_org', 0),

            'ttl_exp_qty_split_odr' => (int)data_get($model, 'split_odrs.ttl_exp_qty', 0),
            'ttl_exp_ctn_split_odr' => (int)data_get($model, 'split_odrs.ttl_exp_ctn', 0),
            'ttl_act_qty_split_odr' => (int)data_get($model, 'split_odrs.total_act_qty', 0),
            'ttl_act_ctn_split_odr' => (int)data_get($model, 'split_odrs.total_act_ctn', 0),

            'ttl_exp_qty_combine_odr' => (int)data_get($model, 'combine_odrs.ttl_exp_qty', 0),
            'ttl_exp_ctn_combine_odr' => (int)data_get($model, 'combine_odrs.ttl_exp_ctn', 0),
            'ttl_act_qty_combine_odr' => (int)data_get($model, 'combine_odrs.ttl_act_qty', 0),
            'ttl_act_ctn_combine_odr' => (int)data_get($model, 'combine_odrs.ttl_act_ctn', 0),
        ];
    }
}
