<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\OrderOutSortDTO;
use App\Validators\AbstractValidator;

class OrderOutSortValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'odr_hdr_ids' => 'required|array',
        ];
    }

    public function messages($params = [])
    {
        return [
        ];
    }

    public function toDTO()
    {
        return OrderOutSortDTO::fromRequest();
    }
}
