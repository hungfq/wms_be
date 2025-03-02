<?php

namespace App\Modules\MasterData\DTO\Item;

use App\Entities\Item;
use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ItemStoreDTO extends FlexibleDataTransferObject
{
    public $sku;
    public $item_name;
    public $pack_size;

    public $serial;
    public $cus_id;
    public $uom_id;
    public $des;
    public $cat_code;
    public $status;
    public $m3;
    public $price_suggest;

    // old required field
    public $size;
    public $color;


    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'sku' => $request->input('sku'),
            'item_name' => $request->input('item_name'),
            'pack_size' => $request->input('pack_size'),
            'serial' => $request->input('serial'),
            'cus_id' => $request->input('cus_id'),
            'uom_id' => $request->input('uom_id'),
            'des' => $request->input('des'),
            'cat_code' => $request->input('cat_code'),
            'status' => $request->input('status'),
            'm3' => $request->input('m3'),
            'price_suggest' => $request->input('price_suggest'),

            'size' => Item::DEFAULT_SIZE,
            'color' => Item::DEFAULT_COLOR,
        ]);
    }
}
