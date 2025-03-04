<?php

namespace App\Modules\Outbound\Validators;

use App\Modules\Outbound\DTO\WavePickPickingDTO;
use App\Validators\AbstractValidator;

class WavePickPickingValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'location_picks' => 'required|array',
            'location_picks.*.loc_code' => 'required',
            'location_picks.*.qty' => 'required|integer',
        ];
    }

    public function toDTO()
    {
        return WavePickPickingDTO::fromRequest();
    }
}
