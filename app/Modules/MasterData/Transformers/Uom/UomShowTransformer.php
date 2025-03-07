<?php

namespace App\Modules\MasterData\Transformers\Uom;

use League\Fractal\TransformerAbstract;

class UomShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => data_get($model, 'id'),
            'code' => data_get($model, 'code'),
            'name' => data_get($model, 'name'),
            'description' => data_get($model, 'description'),
        ];
    }
}
