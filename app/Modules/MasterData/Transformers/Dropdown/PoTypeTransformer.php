<?php

namespace App\Modules\MasterData\Transformers\Dropdown;

use League\Fractal\TransformerAbstract;

class PoTypeTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'code' => $model->code,
        ];
    }
}
