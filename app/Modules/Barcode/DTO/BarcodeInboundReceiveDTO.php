<?php

namespace App\Modules\Barcode\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class BarcodeInboundReceiveDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $po_dtl_id;
    public $qty;
    public $ctn_ttl;
    public $loc_code;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'po_dtl_id' => $request->input('po_dtl_id'),
            'qty' => $request->input('qty'),
            'ctn_ttl' => $request->input('ctn_ttl'),
            'loc_code' => $request->input('loc_code'),
        ]);
    }
}