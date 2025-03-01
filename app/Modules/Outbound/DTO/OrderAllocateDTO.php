<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderAllocateDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $odr_hdr_ids;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'odr_hdr_ids' => $request->input('odr_hdr_ids'),
        ]);
    }
}
