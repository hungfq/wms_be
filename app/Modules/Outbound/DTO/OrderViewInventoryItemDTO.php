<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderViewInventoryItemDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $cus_id;
    public $sku;
    public $bin_loc_id;
    public $page;
    public $limit;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'cus_id' => $request->input('cus_id'),
            'sku' => $request->input('sku'),
            'bin_loc_id' => $request->input('bin_loc_id'),
            'page' => $request->input('page'),
            'limit' => $request->input('limit'),
        ]);
    }
}
