<?php

namespace App\Modules\Inbound\DTO\PO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class PoViewDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $po_sts;

    public $export_type;
    public $limit;
    public $page;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'po_sts' => $request->input('po_sts'),

            'export_type' => $request->input('export_type'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}