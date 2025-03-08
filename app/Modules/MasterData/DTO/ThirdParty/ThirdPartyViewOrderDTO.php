<?php

namespace App\Modules\MasterData\DTO\ThirdParty;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ThirdPartyViewOrderDTO extends FlexibleDataTransferObject
{
    public $tp_id;
    public $limit;
    public $page;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'tp_id' => $request->input('tp_id'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}
