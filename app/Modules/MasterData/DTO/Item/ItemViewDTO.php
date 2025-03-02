<?php

namespace App\Modules\MasterData\DTO\Item;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ItemViewDTO extends FlexibleDataTransferObject
{
    public $cus_id;
    public $sku;
    public $item_name;
    public $pack_size;
    public $uom_id;
    public $cat_code;
    public $group_id;
    public $subsidiary_id;
    public $item_class_id;
    public $item_status_id;
    public $status;
    public $limit;
    public $sort;
    public $export_type;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'cus_id' => $request->input('cus_id'),
            'sku' => $request->input('sku'),
            'item_name' => $request->input('item_name'),
            'pack_size' => $request->input('pack_size'),
            'uom_id' => $request->input('uom_id'),
            'cat_code' => $request->input('cat_code'),
            'group_id' => $request->input('group_id'),
            'subsidiary_id' => $request->input('subsidiary_id'),
            'item_class_id' => $request->input('item_class_id'),
            'item_status_id' => $request->input('item_status_id'),
            'status' => $request->input('status'),
            'limit' => $request->input('limit'),
            'sort' => $request->input('sort'),
            'export_type' => $request->input('export_type'),
        ]);
    }
}
