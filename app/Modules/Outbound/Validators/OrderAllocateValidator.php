<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\OrderAllocateDTO;
use App\Validators\AbstractValidator;

class OrderAllocateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required|integer',
            'odr_hdr_ids' => 'required|array',
        ];
    }

    public function messages($params = [])
    {
        return [];
    }

    public function toDTO()
    {
        return OrderAllocateDTO::fromRequest();
    }
}
