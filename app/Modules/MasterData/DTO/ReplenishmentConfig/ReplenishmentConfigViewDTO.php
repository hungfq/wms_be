<?php

namespace App\Modules\MasterData\DTO\ReplenishmentConfig;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ReplenishmentConfigViewDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $sku;
    public $item_name;
    public $is_warning;
    public $export_type;
    public $limit;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'sku' => $request->input('sku'),
            'item_name' => $request->input('item_name'),
            'is_warning' => $request->input('is_warning'),
            'export_type' => $request->input('export_type'),
            'limit' => $request->input('limit'),
            'sort' => $request->input('sort'),
        ]);
    }
}
