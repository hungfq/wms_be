<?php

namespace App\Modules\Auth\DTO;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class AuthLoginUserPassDTO extends FlexibleDataTransferObject
{
    public $email;
    public $password;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);
    }

}