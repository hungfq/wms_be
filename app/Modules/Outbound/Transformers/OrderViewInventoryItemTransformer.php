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
            'item_name' => data_get($model, 'item.item_name'),
            'pack_size' => data_get($model, 'item.pack_size'),
            'price_suggest' => data_get($model, 'item.price_suggest'),
            'm3' => data_get($model, 'item.m3'),
            'lots' => data_get($model, 'lots')
        ];
    }
}
