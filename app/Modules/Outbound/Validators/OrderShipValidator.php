<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\OrderShipDTO;
use App\Validators\AbstractValidator;

class OrderShipValidator extends AbstractValidator
{
    /**
     * @param array $params
     * @return array
     */
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required|integer',
            'odr_hdr_ids' => 'required|array',
            'shipped_dt' => 'required|date'
        ];
    }

    public function toDTO()
    {
        return OrderShipDTO::fromRequest();
    }
}
