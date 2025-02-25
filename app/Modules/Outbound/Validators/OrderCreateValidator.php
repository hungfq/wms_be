<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\OrderCreateDTO;
use App\Validators\AbstractValidator;

class OrderCreateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required|integer',

            'cus_id' => 'required|integer',
            'cus_odr_num' => 'required|string|max:50', //so_no
            'cus_po' => 'required|string|max:1000', // do_no
            'odr_type_id' => 'required',
            'department_id' => 'nullable|integer',

            'carrier' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:30',
            'truck_no' => 'nullable|string|max:15',
            'container_no' => 'nullable|string|max:50',
            'container_type_id' => 'nullable|integer',
            'seal_no' => 'nullable|string|max:50',

            'ship_by_dt' => 'nullable',
            'code' => 'required',
            'ship_to_name' => 'required',
            'ship_to_add' => 'required',
            'ship_to_city' => 'nullable',
            'ship_to_country' => 'nullable',
            'ship_to_state' => 'nullable',
            'details' => ['required', 'array'],
            'details.*.item_id' => 'required',
            'details.*.bin_loc_id' => 'nullable',
            'details.*.lot' => ['required', 'string', 'max:10'],
            'details.*.ctn_ttl' => ['required', 'integer', 'min:1'],
            'details.*.piece_qty' => ['required', 'integer', 'min:1'],

            'sil_no' => 'nullable|string|max:15',
            'bl_no' => 'nullable|string|max:50',
            'job_no' => 'nullable|string|max:50',
            'invoice_no' => 'nullable|string|max:1000',

            "custbody_scv_hrv_num" => "nullable|string",
            "custbody_scv_cus_code_hrv" => "nullable|string",
            "custbody_scv_hrv_cus" => "nullable|string",
            "custbody_scv_hrv_phone" => "nullable|string",
            "custbody_scv_hrv_fb" => "nullable|string",
            "custbody_scv_creator_hrv" => "nullable|string",
            "custbody_scv_street_hrv" => "nullable|string",
            "custbody_scv_ward_hrv" => "nullable|string",
            "custbody_scv_district_hrv" => "nullable|string",
            "custbody_scv_province_hrv" => "nullable|string",
            "custbody_scv_country_code_hrv" => "nullable|string",
            "custbody_scv_hrv_add" => "nullable|string",
            "custbody_scv_source_hrv" => "nullable|string",
            "custbody_scv_tracking_company" => "nullable|string",
            "custbody_scv_tracking_numbers" => "nullable|string",
            "custbody_scv_tax_num_kv" => "nullable|string",
        ];
    }

    public function toDTO()
    {
        return OrderCreateDTO::fromRequest();
    }
}
