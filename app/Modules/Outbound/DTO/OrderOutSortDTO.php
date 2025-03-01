<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderOutSortDTO extends FlexibleDataTransferObject
{
    public $odr_hdr_ids;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'odr_hdr_ids' => $request->input('odr_hdr_ids') ?? [],
        ]);
    }
}