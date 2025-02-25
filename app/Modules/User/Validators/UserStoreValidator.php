<?php

namespace App\Modules\User\Validators;

use App\Modules\User\DTO\UserStoreDTO;
use App\Validators\AbstractValidator;


class UserStoreValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'type' => 'required|string',
            'email' => 'required|string',
            'code' => 'required|string',
            'name' => 'required|string',
            'gender' => 'nullable',
            'picture' => 'nullable',
        ];
    }

    public function toDTO()
    {
        return UserStoreDTO::fromRequest();
    }
}
