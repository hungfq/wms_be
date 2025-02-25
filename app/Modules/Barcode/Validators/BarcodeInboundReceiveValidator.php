<?php

namespace App\Modules\Barcode\Validators;

use App\Modules\Barcode\DTO\BarcodeInboundReceiveDTO;
use App\Validators\AbstractValidator;

class BarcodeInboundReceiveValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            // Receiving Rule
            'whs_id' => 'required|integer',
            'po_dtl_id' => 'required|integer',
            'qty' => 'required|integer|min:1',
            'ctn_ttl' => 'required|integer|min:1',

            // Put-away Rule
            'loc_code' => 'required',
        ];
    }

    public function messages($params = [])
    {
        return [
        ];
    }

    public function toDTO()
    {
        return BarcodeInboundReceiveDTO::fromRequest();
    }
}
