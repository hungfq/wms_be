<?php

namespace App\Modules\MasterData\DTO\User;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UserViewDTO extends FlexibleDataTransferObject
{
    public $email;
    public $user_name;
    public $status;
    public $first_name;
    public $last_name;
    public $page;
    public $limit;
    public $sort;
    public $export_type;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'email' => $request->input('email'),
            'user_name' => $request->input('user_name'),
            'status' => $request->input('status'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'page' => $request->input('page'),
            'limit' => $request->input('limit'),
            'sort' => $request->input('sort'),
            'export_type' => $request->input('export_type'),
        ]);
    }
}
