<?php

namespace App\Modules\MasterData\Transformers\Dropdown;

use League\Fractal\TransformerAbstract;

class BinLocTransformer extends TransformerAbstract
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
