<?php

namespace App\Modules\Inbound\Validators\PO;

use App\Modules\Inbound\DTO\PO\PoUpdateDTO;
use App\Validators\AbstractValidator;

class PoUpdateValidator extends AbstractValidator
{
    /**
     * @param array $params
     * @return array
     */
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required|integer',
            'po_hdr_id' => 'required|integer',
            'po_type' => 'required',
            'seal_number' => 'nullable',
            'ref_code' => 'nullable',
            'expt_date' => 'nullable|date',
            'arrived_date' => 'nullable|date',
            'po_no' => 'nullable',
            'container_code' => 'nullable',
            'container_id' => 'nullable|integer',
            'container_type_id' => 'nullable|integer',
            'odr_hdr_id' => 'nullable|integer',
            'from_whs_id' => 'nullable|integer',
            'to_whs_id' => 'nullable|integer',
            'des' => 'nullable',
            'cus_id' => 'required|integer',
            'mot' => 'nullable|string',
            'internal_id' => 'nullable|integer',
            'bl_no' => 'nullable|string|max:50',
            'voy_no' => 'nullable|string|max:50',
            'vessel' => 'nullable|string|max:50',
            'departure' => 'nullable|string|max:50',
            'invoice_no' => 'nullable|string|max:50',
            'from_vendor_id' => 'nullable|integer',
            'final_des' => 'nullable|string|max:150',
            'arrival' => 'nullable|string|max:50',
            'is_mix_model' => 'required|integer',
            'details' => 'nullable|array',
            'details.*.item_id' => 'required|integer',
            'details.*.bin_loc_id' => 'required|integer',
            'details.*.po_dtl_id' => 'nullable|integer',
            'details.*.remark' => 'nullable|string|max:100',
        ];
    }

    public function toDTO()
    {
        return PoUpdateDTO::fromRequest();
    }
}
