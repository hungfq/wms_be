<?php

namespace App\Modules\WhsConfig\Transformers;

use League\Fractal\TransformerAbstract;

class LocationShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'loc_id' => $model->loc_id,
            'whs_id' => $model->whs_id,
            'loc_code' => $model->loc_code,
            'loc_name' => $model->loc_name,
            'loc_type_id' => $model->loc_type_id,
            'zone_id' => $model->zone_id,
            'length' => $model->length,
            'width' => $model->width,
            'height' => $model->height,
            'des' => $model->des,
            'aisle' => $model->aisle,
            'level' => $model->level,
            'row' => $model->row,
            'bin' => $model->bin,
            'loc_sts' => $model->loc_sts,
            'max_pallet' => $model->max_pallet,
            'is_full' => $model->is_full,
            'can_mix_sku' => $model->can_mix_sku,
        ];
    }
}
