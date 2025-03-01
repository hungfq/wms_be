<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class WavePickPickingDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $wv_hdr_id;
    public $wv_dtl_id;
    public $location_picks;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'wv_hdr_id' => $request->input('wv_hdr_id'),
            'wv_dtl_id' => $request->input('wv_dtl_id'),
            'location_picks' => $request->input('location_picks'),
        ]);
    }
}