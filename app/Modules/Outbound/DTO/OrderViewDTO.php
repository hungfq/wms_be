<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderViewDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $cus_id;
    public $odr_num;
    public $cus_odr_num;
    public $cus_po;
    public $odr_sts;
    public $sku;
    public $wv_num;
    public $ship_to_code;
    public $ship_to_name;
    public $created_at_from;
    public $created_at_to;
    public $act_shipped_date_from;
    public $act_shipped_date_to;
    public $is_drop_orders;

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
            'odr_num' => $request->input('odr_num'),
            'cus_odr_num' => $request->input('cus_odr_num'),
            'cus_po' => $request->input('cus_po'),
            'odr_sts' => $request->input('odr_sts'),
            'sku' => $request->input('sku'),
            'wv_num' => $request->input('wv_num'),
            'ship_to_code' => $request->input('ship_to_code'),
            'ship_to_name' => $request->input('ship_to_name'),
            'created_at_from' => $request->input('created_at_from'),
            'created_at_to' => $request->input('created_at_to'),
            'act_shipped_date_from' => $request->input('act_shipped_date_from'),
            'act_shipped_date_to' => $request->input('act_shipped_date_to'),
            'is_drop_orders' => $request->input('is_drop_orders'),
            'export_type' => $request->input('export_type'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
            'sort' => $request->input('sort'),
        ]);
    }
}