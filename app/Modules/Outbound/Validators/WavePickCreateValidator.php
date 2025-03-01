<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\WavePickCreateDTO;
use App\Validators\AbstractValidator;

class WavePickCreateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required',
            'odr_hdr_ids' => 'required|array',
        ];
    }

    public function toDTO()
    {
        return WavePickCreateDTO::fromRequest();
    }
}