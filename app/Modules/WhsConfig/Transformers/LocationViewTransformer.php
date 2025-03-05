<?php

namespace App\Modules\WhsConfig\Transformers;

use League\Fractal\TransformerAbstract;

class LocationViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'loc_id' => $model->loc_id,
            'whs_id' => $model->whs_id,
            'loc_code' => $model->loc_code,
            'loc_name' => $model->loc_name,
            'loc_type_id' => $model->loc_type_id,
            'loc_type_name' => $model->loc_type_name,
            'zone_id' => $model->zone_id,
            'zone_name' => $model->zone_name,
            'rfid' => $model->rfid,
            'length' => $model->length,
            'width' => $model->width,
            'height' => $model->height,
            'des' => $model->des,
            'aisle' => $model->aisle,
            'level' => $model->level,
            'row' => $model->row,
            'bin' => $model->bin,
            'loc_sts' => $model->loc_sts,
            'loc_sts_name' => data_get($model, 'statuses.sts_name'),
            'max_pallet' => $model->max_pallet,
            'is_full' => $model->is_full,
            'can_mix_sku' => $model->can_mix_sku,
            'created_at' => $model->created_at,
            'created_by' => $model->created_by,
            'created_by_name' => data_get($model, 'created_by_name'),
            'updated_at' => $model->updated_at,
            'updated_by' => $model->updated_by,
            'updated_by_name' => data_get($model, 'updated_by_name'),
        ];
    }
}
