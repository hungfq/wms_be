<?php

namespace App\Modules\Inbound\Validators\PO;

use App\Entities\Customer;
use App\Entities\Warehouse;
use App\Libraries\Language;
use App\Modules\Inbound\DTO\PO\PoStoreDTO;
use App\Validators\AbstractValidator;

class PoStoreValidator extends AbstractValidator
{
    /**
     * @param array $params
     * @return array
     */
    public function rules($params = [])
    {
        return [
            'ref_code' => [
                'nullable',
            ],
            'whs_id' => [
                'required',
                'exists:' . Warehouse::getTableName() . ',whs_id,deleted, 0'
            ],
            'cus_id' => [
                'required',
                'exists:' . Customer::getTableName() . ',cus_id,deleted,0',
            ],
            'po_type' => 'required',
            'seal_number' => 'nullable',
            'expt_date' => 'nullable|date',
            'po_no' => 'nullable',
            'container_code' => 'nullable',
            'container_id' => 'nullable|integer',
            'container_type_id' => 'nullable|integer',
            'odr_hdr_id' => 'nullable|integer',
            'from_whs_id' => 'nullable|integer',
            'to_whs_id' => 'nullable|integer',
            'des' => 'nullable',
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
            'details' => 'required|array',
            'details.*.item_id' => 'required',
            'details.*.bin_loc_id' => 'required',
            'details.*.lot' => 'required|max:50',
            'details.*.item_lot' => 'distinct',
            'details.*.exp_qty' => 'required|numeric|gt:0',
            'details.*.exp_ctn_ttl' => 'required|numeric|gt:0',
            'details.*.remark' => 'nullable|string|max:100',
        ];
    }

    public function toDTO()
    {
        return PoStoreDTO::fromRequest();
    }

    /**
     * @param array $params
     * @return array
     */
    public function messages($params = [])
    {
        $this->_isTransformMsg = true;

        return [
            'ref_code' => [
                'required' => Language::translate('{0} is required.', 'Ref code')
            ],
            'whs_id' => [
                'required' => Language::translate('{0} is required.', 'Warehouse'),
                'exists' => Language::translate('{0} is not exist', 'Warehouse'),
            ],
            'cus_id' => [
                'required' => Language::translate('{0} is required.', 'Customer'),
                'exists' => Language::translate('{0} is not exist', 'Customer'),
            ],
            'expt_date' => [
                'required' => Language::translate('{0} is required.', 'Expert date'),
            ],
            'details.*.item_id' => [
                'required' => Language::translate('{0} is required.', 'Item'),
                'exists' => Language::translate('{0} is not exist', 'Item'),
            ],
            'details.*.lot' => [
                'required' => Language::translate('{0} is required.', 'Lot'),
                'max' => Language::translate('The {0} may not be greater than {1} characters.', 'Lot', ':max'),
            ],
            'details.*.item_lot' => [
                'distinct' => Language::translate('Item and Lot is unique.'),
            ],
            'details.*.exp_qty' => [
                'required' => Language::translate('{0} is required.', 'Expect Qty'),
                'numeric' => Language::translate('The {0} must be a number.', 'Expect Qty'),
                'gt' => Language::translate('The {0} must be greater than :value.', 'Expect Qty', ':value'),
            ],
            'details.*.exp_ctn_ttl' => [
                'required' => Language::translate('{0} is required.', 'Expect Carton total'),
                'numeric' => Language::translate('The {0} must be a number.', 'Expect Carton total'),
                'gt' => Language::translate('The {0} must be greater than :value.', 'Expect Carton total', ':value'),
            ],
            'po_type' => [
                'required' => Language::translate('{0} is required.', 'Po Type')
            ],
        ];
    }
}
