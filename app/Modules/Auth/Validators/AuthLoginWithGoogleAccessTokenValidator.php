<?php

namespace App\Modules\Auth\Validators;

use App\Modules\Auth\DTO\AuthLoginWithGoogleAccessTokenDTO;
use App\Validators\AbstractValidator;

class AuthLoginWithGoogleAccessTokenValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'access_token' => 'required',
            'type' => 'required',
        ];
    }

    public function toDTO()
    {
        return AuthLoginWithGoogleAccessTokenDTO::fromRequest();
    }
}