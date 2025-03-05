<?php

namespace App\Modules\WhsConfig\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class LocationViewDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $loc_code;
    public $loc_name;
    public $loc_sts;

    public $export_type;
    public $limit;
    public $page;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'loc_code' => $request->input('loc_code'),
            'loc_name' => $request->input('loc_name'),
            'loc_sts' => $request->input('loc_sts'),

            'export_type' => $request->input('export_type'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}