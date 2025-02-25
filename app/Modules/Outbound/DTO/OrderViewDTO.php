<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderViewDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $cus_id;
    public $wv_hdr_num;
    public $sku;
    public $gr_hdr_num;
    public $po_num;
    public $ref_code;
    public $gr_hdr_sts;
    public $act_date_from;
    public $act_date_to;
    public $expt_date_from;
    public $expt_date_to;

    public $export_type;
    public $limit;
    public $page;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'cus_id' => $request->input('cus_id'),
            'wv_hdr_num' => $request->input('wv_hdr_num'),
            'sku' => $request->input('sku'),
            'gr_hdr_num' => $request->input('gr_hdr_num'),
            'po_num' => $request->input('po_num'),
            'ref_code' => $request->input('ref_code'),
            'gr_hdr_sts' => $request->input('gr_hdr_sts'),
            'act_date_from' => $request->input('act_date_from'),
            'act_date_to' => $request->input('act_date_to'),
            'expt_date_from' => $request->input('expt_date_from'),
            'expt_date_to' => $request->input('expt_date_to'),
            'export_type' => $request->input('export_type'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}