<?php

namespace App\Modules\MasterData\DTO\ThirdParty;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ThirdPartyViewWalletDTO extends FlexibleDataTransferObject
{
    public $tp_id;
    public $code;
    public $name;
    public $phone;
    public $limit;
    public $page;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'tp_id' => $request->input('tp_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}
