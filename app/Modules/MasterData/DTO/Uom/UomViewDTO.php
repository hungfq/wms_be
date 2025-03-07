<?php

namespace App\Modules\MasterData\DTO\Uom;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UomViewDTO extends FlexibleDataTransferObject
{
    public $code;
    public $name;
    public $limit;
    public $page;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}
