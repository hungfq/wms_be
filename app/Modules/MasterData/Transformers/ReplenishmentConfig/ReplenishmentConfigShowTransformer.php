<?php

namespace App\Modules\MasterData\Transformers\ReplenishmentConfig;

use League\Fractal\TransformerAbstract;

class ReplenishmentConfigShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => data_get($model, 'id'),
            'item_id' => data_get($model, 'item_id'),
            'sku' => data_get($model, 'item.sku'),
            'item_name' => data_get($model, 'item.item_name'),
            'min_qty' => data_get($model, 'min_qty'),
            'des' => data_get($model, 'des'),
        ];
    }
}
