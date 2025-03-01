<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\OrderUpdateDTO;
use App\Validators\AbstractValidator;

class OrderUpdateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'odr_hdr_id' => 'required|integer',
            'whs_id' => 'required|integer',

            'cus_id' => 'required|integer',
            'cus_odr_num' => 'required|string|max:50', //so_no
            'cus_po' => 'required|string|max:1000', // do_no
            'odr_type_id' => 'required',
            'department_id' => 'nullable|integer',

            'carrier' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:30',
            'truck_no' => 'nullable|string|max:15',
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
            'details.*.lot' => ['required', 'max:50'],
            'details.*.ctn_ttl' => ['required', 'integer', 'min:1'],
            'details.*.piece_qty' => ['required', 'integer', 'min:1'],

            'bl_no' => 'nullable|string|max:50',
            'invoice_no' => 'nullable|string|max:1000',
        ];

    }

    public function toDTO()
    {
        return OrderUpdateDTO::fromRequest();
    }
}
