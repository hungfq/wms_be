<?php

namespace App\Modules\MasterData\Transformers\Uom;

use League\Fractal\TransformerAbstract;

class UomViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => data_get($model, 'id'),
            'code' => data_get($model, 'code'),
            'name' => data_get($model, 'name'),
            'description' => data_get($model, 'description'),
            'created_at' => data_get($model, 'created_at'),
            'created_by_name' => data_get($model, 'created_by_name'),
            'updated_at' => data_get($model, 'updated_at'),
            'updated_by_name' => data_get($model, 'updated_by_name'),
        ];
    }
}
