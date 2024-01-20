<?php

namespace App\Modules\Auth\Transformers;

use League\Fractal\TransformerAbstract;

class UserGetPermissionTransformer extends TransformerAbstract {

    public function transform($model)
    {
        return [
            'name' => $model->name
        ];
    }
}