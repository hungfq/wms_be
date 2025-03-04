<?php

namespace App\Modules\MasterData\DTO\User;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UserViewDTO extends FlexibleDataTransferObject
{
    public $cus_id;
    public $code;
    public $name;
    public $page;
    public $phone;
    public $limit;
    public $sort;
    public $export_type;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'cus_id' => $request->input('cus_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'page' => $request->input('page'),
            'phone' => $request->input('phone'),
            'limit' => $request->input('limit'),
            'sort' => $request->input('sort'),
            'export_type' => $request->input('export_type'),
        ]);
    }
}
