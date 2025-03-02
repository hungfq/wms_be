<?php

namespace App\Modules\MasterData\Transformers\Item;

use League\Fractal\TransformerAbstract;

class ItemShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'item_id' => data_get($model, 'item_id'),
            'cus_id' => data_get($model, 'cus_id'),
            'status' => data_get($model, 'status'),
            'sku' => data_get($model, 'sku'),
            'item_name' => data_get($model, 'item_name'),
            'uom_id' => data_get($model, 'uom_id'),
            'uom_name' => data_get($model, 'uom_name'),
            'cat_code' => data_get($model, 'cat_code'),
            'cat_name' => data_get($model, 'cat_name'),
            'm3' => data_get($model, 'm3'),
            'pack_size' => data_get($model, 'pack_size'),
            'des' => data_get($model, 'des'),
            'price_suggest' => data_get($model, 'price_suggest'),
            'created_at' => $model->created_at,
            'created_by_name' => data_get($model, 'created_by_name'),
            'updated_at' => $model->updated_at,
            'updated_by_name' => data_get($model, 'updated_by_name')
        ];
    }
}
