<?php

namespace App\Modules\Auth\Transformers;

use League\Fractal\TransformerAbstract;

class UserGetProfileTransformer extends TransformerAbstract {

    public function transform($user)
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'user_name' => $user->name,
            'gender' => data_get($user, 'profile.gender'),
            'full_name' => data_get($user, 'profile.full_name'),
            'first_name' => data_get($user, 'profile.first_name'),
            'last_name' => data_get($user, 'profile.last_name'),
            'image' => data_get($user, 'profile.image'),
            'contact_email' => data_get($user, 'profile.contact_email'),
            'contact_phone' => data_get($user, 'profile.contact_phone'),
            'info' => $user->info
        ];
    }
}