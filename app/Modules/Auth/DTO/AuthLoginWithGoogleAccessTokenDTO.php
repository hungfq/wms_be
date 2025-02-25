<?php

namespace App\Modules\Auth\DTO;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class AuthLoginWithGoogleAccessTokenDTO extends FlexibleDataTransferObject
{
    public $access_token;
    public $type;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'access_token' => $request->input('access_token'),
            'type' => $request->input('type'),
        ]);
    }

}