<?php

namespace App\Modules\Auth\Validators;

use App\Modules\Auth\DTO\AuthLoginUserPassDTO;
use App\Validators\AbstractValidator;

class AuthLoginUserPassValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'email' => 'required',
            'password' => 'required|string',
        ];
    }

    public function toDTO()
    {
        return AuthLoginUserPassDTO::fromRequest();
    }
}