<?php

namespace App\Modules\Inbound\DTO\PO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class PoViewDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $po_sts;
    public $po_num;
    public $po_type;
    public $po_no;
    public $sku;
    public $invoice_no;
    public $created_at_from;
    public $created_at_to;
    public $updated_at_from;
    public $updated_at_to;
    public $from_vendor_name;

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
            'po_num' => $request->input('po_num'),
            'po_type' => $request->input('po_type'),
            'po_no' => $request->input('po_no'),
            'sku' => $request->input('sku'),
            'invoice_no' => $request->input('invoice_no'),
            'created_at_from' => $request->input('created_at_from'),
            'created_at_to' => $request->input('created_at_to'),
            'updated_at_from' => $request->input('updated_at_from'),
            'updated_at_to' => $request->input('updated_at_to'),
            'from_vendor_name' => $request->input('from_vendor_name'),

            'export_type' => $request->input('export_type'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}