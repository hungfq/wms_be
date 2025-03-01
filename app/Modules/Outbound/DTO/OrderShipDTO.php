<?php

namespace App\Modules\Outbound\DTO;

use Carbon\Carbon;
use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderShipDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $odr_hdr_ids;
    public $shipped_dt;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'odr_hdr_ids' => $request->input('odr_hdr_ids'),
            'shipped_dt' => $request->input('shipped_dt') ?? Carbon::now(),
        ]);
    }
}
