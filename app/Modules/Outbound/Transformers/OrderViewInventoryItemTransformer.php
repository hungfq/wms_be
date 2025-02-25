<?php

namespace App\Modules\Outbound\Transformers;

use League\Fractal\TransformerAbstract;

class OrderViewInventoryItemTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'item_id' => data_get($model, 'item_id'),
            'sku' => data_get($model, 'item.sku'),
            'item_code' => data_get($model, 'item.item_code'),
            'item_name' => data_get($model, 'item.item_name'),
            'size' => data_get($model, 'item.size'),
            'color' => data_get($model, 'item.color'),
            'pack_size' => data_get($model, 'item.pack_size'),
            'serial' => data_get($model, 'item.serial'),
            'm3' => data_get($model, 'item.m3'),
            'lots' => data_get($model, 'lots')
        ];
    }
}
