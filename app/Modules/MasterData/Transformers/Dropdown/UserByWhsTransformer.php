<?php

namespace App\Modules\MasterData\Transformers\Dropdown;

use League\Fractal\TransformerAbstract;

class UserByWhsTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'user_id' => $model->id,
            'email' => $model->email,
            'name' => $model->name,
            'full_name' => data_get($model, 'profile.full_name')
        ];
    }
}
