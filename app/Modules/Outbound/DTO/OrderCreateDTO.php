<?php

namespace App\Modules\Outbound\DTO;

use App\Entities\OrderHdr;
use Spatie\DataTransferObject\FlexibleDataTransferObject;

class OrderCreateDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $cus_id;
    public $cus_odr_num;
    public $cus_po;
    public $odr_type_id;
    public $department_id;

    public $carrier;
    public $driver_info;
    public $truck_num;
    public $seal_num;

    public $ship_by_dt;
    public $ship_to_name;
    public $ship_to_add;
    public $ship_to_city;
    public $ship_to_country;
    public $ship_to_state;
    public $code;
    public $vat_code;
    public $zip_code;
    public $fax;
    public $phone;
    public $tp_id;
    public $cancel_by_dt;
    public $in_notes;
    public $cus_notes;
    public $details;
    public $state_id;

    public $bl_no;
    public $job_no;
    public $invoice_no;
    public $amount;

    // old fields
    public $odr_type;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'cus_id' => $request->input('cus_id'),
            'cus_odr_num' => $request->input('cus_odr_num'),
            'cus_po' => $request->input('cus_po'),
            'odr_type_id' => $request->input('odr_type_id'),
            'department_id' => $request->input('department_id'),

            'carrier' => $request->input('carrier'),
            'driver_info' => $request->input('driver_name'),
            'truck_num' => $request->input('truck_no'),
            'seal_num' => $request->input('seal_no'),

            'tp_id' => $request->input('tp_id'),
            'ship_to_name' => $request->input('ship_to_name'),
            'ship_to_add' => $request->input('ship_to_add'),
            'ship_to_city' => $request->input('ship_to_city'),
            'ship_to_country' => $request->input('ship_to_country'),
            'ship_to_state' => $request->input('ship_to_state'),
            'code' => $request->input('code'),
            'zip_code' => $request->input('zip_code'),
            'fax' => $request->input('fax'),
            'phone' => $request->input('phone'),
            'vat_code' => $request->input('vat_code'),

            'ship_by_dt' => $request->input('ship_by_dt'),
            'cancel_by_dt' => $request->input('cancel_by_dt'),
            'in_notes' => $request->input('in_notes'),
            'cus_notes' => $request->input('cus_notes'),
            'details' => $request->input('details'),
            'state_id' => $request->input('state_id'),

            'bl_no' => $request->input('bl_no'),
            'job_no' => $request->input('job_no'),
            'invoice_no' => $request->input('invoice_no'),
            'amount' => $request->input('total_line_amount'),

            'odr_type' => $request->input('odr_type') ?? OrderHdr::TYPE_BULK,
        ]);
    }
}
