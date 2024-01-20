<?php

namespace App\Modules\User\DTO;

use App\Entities\User;
use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UserUpdateDTO extends FlexibleDataTransferObject
{
    public $id;
    public $email;
    public $code;
    public $name;
    public $gender;
//    public $picture;
    public $status;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'id' => $request->input('id'),
            'email' => $request->input('email'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'gender' => $request->input('gender'),
//            'picture' => $request->input('picture'),
            'status' => $request->input('status') ?? User::STATUS_ACTIVE,
        ]);
    }
}