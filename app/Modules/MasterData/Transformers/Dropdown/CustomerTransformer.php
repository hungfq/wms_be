<?php

namespace App\Modules\MasterData\Transformers\Dropdown;

use League\Fractal\TransformerAbstract;

class CustomerTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'cus_id' => $model->cus_id,
            'cus_name' => $model->name,
            'cus_code' => $model->code,
        ];
    }
}
