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
    public $ref_cod;
    public $rush_odr;
    public $odr_type_id;
    public $department_id;

    public $carrier;
    public $driver_info;
    public $truck_num;
    public $container_num;
    public $container_type_id;
    public $seal_num;
    public $tracking_num;

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
    public $req_cmpl_dt;
    public $act_cmpl_dt;
    public $act_cancel_dt;
    public $in_notes;
    public $cus_notes;
    public $odr_flows;
    public $transfer_whs_id;
    public $details;
    public $state_id;

    public $sil_no;
    public $bl_no;
    public $job_no;
    public $invoice_no;
    public $invoice_date;
    public $zip_no;
    public $custbody_scv_hrv_num;
    public $custbody_scv_cus_code_hrv;
    public $custbody_scv_hrv_cus;
    public $custbody_scv_hrv_phone;
    public $custbody_scv_hrv_fb;
    public $custbody_scv_creator_hrv;
    public $custbody_scv_street_hrv;
    public $custbody_scv_ward_hrv;
    public $custbody_scv_district_hrv;
    public $custbody_scv_province_hrv;
    public $custbody_scv_country_code_hrv;
    public $custbody_scv_hrv_add;
    public $custbody_scv_source_hrv;
    public $custbody_scv_tracking_company;
    public $custbody_scv_tracking_numbers;
    public $custbody_scv_tax_num_kv;
    public $vehicle_id;

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
            'ref_cod' => $request->input('ref_cod'),
            'rush_odr' => $request->input('rush_odr'),
            'odr_type_id' => $request->input('odr_type_id'),
            'department_id' => $request->input('department_id'),

            'carrier' => $request->input('carrier'),
            'driver_info' => $request->input('driver_name'),
            'truck_num' => $request->input('truck_no'),
            'container_num' => $request->input('container_no'),
            'container_type_id' => $request->input('container_type_id'),
            'seal_num' => $request->input('seal_no'),
            'tracking_num' => $request->input('tracking_num'),

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
            'req_cmpl_dt' => $request->input('req_cmpl_dt'),
            'act_cmpl_dt' => $request->input('act_cmpl_dt'),
            'act_cancel_dt' => $request->input('act_cancel_dt'),
            'in_notes' => $request->input('in_notes'),
            'cus_notes' => $request->input('cus_notes'),
            'odr_flows' => $request->input('odr_flows'),
            'transfer_whs_id' => $request->input('transfer_whs_id'),
            'details' => $request->input('details'),
            'state_id' => $request->input('state_id'),

            'sil_no' => $request->input('sil_no'),
            'bl_no' => $request->input('bl_no'),
            'job_no' => $request->input('job_no'),
            'invoice_no' => $request->input('invoice_no'),
            'invoice_date' => $request->input('invoice_date'),
            'zip_no' => $request->input('zip_no'),

            'custbody_scv_hrv_num' => $request->input('custbody_scv_hrv_num'),
            'custbody_scv_cus_code_hrv' => $request->input('custbody_scv_cus_code_hrv'),
            'custbody_scv_hrv_cus' => $request->input('custbody_scv_hrv_cus'),
            'custbody_scv_hrv_phone' => $request->input('custbody_scv_hrv_phone'),
            'custbody_scv_hrv_fb' => $request->input('custbody_scv_hrv_fb'),
            'custbody_scv_creator_hrv' => $request->input('custbody_scv_creator_hrv'),
            'custbody_scv_street_hrv' => $request->input('custbody_scv_street_hrv'),
            'custbody_scv_ward_hrv' => $request->input('custbody_scv_ward_hrv'),
            'custbody_scv_district_hrv' => $request->input('custbody_scv_district_hrv'),
            'custbody_scv_province_hrv' => $request->input('custbody_scv_province_hrv'),
            'custbody_scv_country_code_hrv' => $request->input('custbody_scv_country_code_hrv'),
            'custbody_scv_hrv_add' => $request->input('custbody_scv_hrv_add'),
            'custbody_scv_source_hrv' => $request->input('custbody_scv_source_hrv'),
            'custbody_scv_tracking_company' => $request->input('custbody_scv_tracking_company'),
            'custbody_scv_tracking_numbers' => $request->input('custbody_scv_tracking_numbers'),
            'custbody_scv_tax_num_kv' => $request->input('custbody_scv_tax_num_kv'),
            'vehicle_id' => $request->input('vehicle_id'),

            'odr_type' => $request->input('odr_type') ?? OrderHdr::TYPE_BULK,
        ]);
    }
}
