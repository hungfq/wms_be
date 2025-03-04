<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\OrderCancelDTO;
use App\Validators\AbstractValidator;

class OrderCancelValidator extends AbstractValidator
{
    /**
     * @param array $params
     * @return array
     */
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required|integer',
            'odr_hdr_id' => 'nullable|integer',
            'odr_hdr_ids' => 'required_without:odr_hdr_id|array'
        ];
    }

    public function toDTO()
    {
        return OrderCancelDTO::fromRequest();
    }
}
