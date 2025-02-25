<?php

namespace App\Modules\Inventory\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class InventoryDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $cus_id;
    public $bin_loc_id;
    public $sku;
    public $lot;
    public $pack_size;
    public $serial;
    public $spc_hdl_code;
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
            'bin_loc_id' => $request->input('bin_loc_id'),
            'sku' => $request->input('sku'),
            'lot' => $request->input('lot'),
            'pack_size' => $request->input('pack_size'),
            'serial' => $request->input('serial'),
            'spc_hdl_code' => $request->input('spc_hdl_code'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'export_type' => $request->input('export_type'),
            'sort' => $request->input('sort'),
        ]);
    }
}
