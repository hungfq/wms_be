<?php

namespace App\Modules\MasterData\DTO\User;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UserStoreDTO extends FlexibleDataTransferObject
{
    public $email;
    public $name;
    public $password;
    public $status;
    public $role_ids;
    public $profile;
    public $user_warehouse;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'name' => $request->input('name'),
            'status' => $request->input('status'),
            'role_ids' => $request->input('role_ids'),
            'profile' => $request->input('profile'),
            'user_warehouse' => $request->input('user_warehouse'),
        ]);
    }
}
