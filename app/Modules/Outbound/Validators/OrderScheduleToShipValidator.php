<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\OrderScheduleToShipDTO;
use App\Validators\AbstractValidator;

class OrderScheduleToShipValidator extends AbstractValidator
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
            'schedule_dt' => 'required|date',
        ];
    }

    public function toDTO()
    {
        return OrderScheduleToShipDTO::fromRequest();
    }
}
