<?php

namespace App\Modules\Inventory\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class InventoryByLocationDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $cus_id;
    public $loc_code;
    public $sku;
    public $bin_loc_id;
    public $limit;
    public $page;
    public $export_type;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'cus_id' => $request->input('cus_id'),
            'loc_code' => $request->input('loc_code'),
            'sku' => $request->input('sku'),
            'bin_loc_id' => $request->input('bin_loc_id'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'export_type' => $request->input('export_type'),
            'sort' => $request->input('sort'),
        ]);
    }
}
