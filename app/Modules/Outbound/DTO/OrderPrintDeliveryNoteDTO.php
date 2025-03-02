<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderPrintDeliveryNoteDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $odr_hdr_id;
    public $with_price;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'odr_hdr_id' => $request->input('odr_hdr_id'),
            'with_price' => $request->input('with_price'),
        ]);
    }
}