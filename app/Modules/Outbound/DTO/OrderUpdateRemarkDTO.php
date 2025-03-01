<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderUpdateRemarkDTO extends FlexibleDataTransferObject
{
    public $odr_hdr_id;
    public $internal_remark;
    public $external_remark;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'odr_hdr_id' => $request->input('odr_hdr_id'),
            'internal_remark' => $request->input('internal_remark'),
            'external_remark' => $request->input('external_remark'),
        ]);
    }
}
