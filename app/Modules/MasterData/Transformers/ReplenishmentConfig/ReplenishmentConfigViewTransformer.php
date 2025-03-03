<?php

namespace App\Modules\MasterData\Transformers\ReplenishmentConfig;

use League\Fractal\TransformerAbstract;

class ReplenishmentConfigViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => data_get($model, 'id'),
            'item_id' => data_get($model, 'item_id'),
            'sku' => data_get($model, 'sku'),
            'item_name' => data_get($model, 'item_name'),
            'min_qty' => data_get($model, 'min_qty'),
            'invt_qty' => data_get($model, 'invt_qty', 0),
            'is_warning' => data_get($model, 'is_warning'),
            'created_at' => $model->created_at,
            'created_by_name' => data_get($model, 'created_by_name'),
            'updated_at' => $model->updated_at,
            'updated_by_name' => data_get($model, 'updated_by_name')
        ];
    }

    public function getTitle()
    {
        return [
            'sku|format_string' => 'Model',
            'item_name|format_string' => 'Model Name',
            'min_qty' => 'Min',
            'invt_qty' => 'Inventory QTY',
            'created_at|format_date' => 'Created Date',
            'created_by_name' => 'Created By',
            'updated_at|format_date' => 'Updated Date',
            'updated_by_name' => 'Updated By',
        ];
    }
}
