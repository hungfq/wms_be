<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class WavePickCancelDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $wv_hdr_ids;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'wv_hdr_ids' => $request->input('wv_hdr_ids') ?? [],
        ]);
    }
}