<?php

namespace App\Modules\MasterData\Transformers\User;

use App\Entities\User;
use App\Libraries\Config;
use League\Fractal\TransformerAbstract;

class UserViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => data_get($model, 'id'),
            'user_name' => data_get($model, 'name'),
            'email' => data_get($model, 'email'),
            'status' => data_get($model, 'status'),
            'status_name' => Config::getStatusName(User::STATUS_KEY, data_get($model, 'status')),
            'first_name' => data_get($model, 'profile.first_name'),
            'gender' => data_get($model, 'profile.gender'),
            'last_name' => data_get($model, 'profile.last_name'),
            'full_name' => data_get($model, 'profile.full_name'),
            'image_url' => data_get($model, 'profile.image'),
            'contact_email' => data_get($model, 'profile.contact_email'),
            'contact_phone' => data_get($model, 'profile.contact_phone'),
            'created_at' => $model->created_at,
        ];
    }
}
