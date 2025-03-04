<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\WavePickCancelDTO;
use App\Validators\AbstractValidator;

class WavePickCancelValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required',
            'wv_hdr_ids' => 'required|array',
        ];
    }

    public function toDTO()
    {
        return WavePickCancelDTO::fromRequest();
    }
}