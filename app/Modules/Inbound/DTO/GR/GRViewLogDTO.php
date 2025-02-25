<?php

namespace App\Modules\Inbound\DTO\GR;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class GRViewLogDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $gr_hdr_id;
    public $search;
    public $limit;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'gr_hdr_id' => $request->input('gr_hdr_id'),
            'search' => $request->input('search'),
            'limit' => $request->input('limit'),
        ]);
    }
}