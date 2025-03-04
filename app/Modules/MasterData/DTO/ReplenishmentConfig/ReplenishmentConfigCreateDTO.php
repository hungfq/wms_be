<?php

namespace App\Modules\MasterData\DTO\ReplenishmentConfig;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ReplenishmentConfigCreateDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $id;
    public $item_id;
    public $min_qty;
    public $des;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'id' => $request->input('id'),
            'item_id' => $request->input('item_id'),
            'min_qty' => $request->input('min_qty'),
            'des' => $request->input('des'),
        ]);
    }
}
