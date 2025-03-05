<?php

namespace App\Modules\WhsConfig\Validators;

use App\Modules\WhsConfig\DTO\LocationUpsertDTO;
use App\Validators\AbstractValidator;

class LocationUpsertValidator extends AbstractValidator
{
    /**
     * @param array $params
     * @return array
     */
    public function rules($params = [])
    {
        return [
            'loc_code' => 'required|max:50',
            'loc_name' => 'required|max:50',
            'loc_type_id' => 'required',
            'zone_id' => 'required',
        ];
    }

    public function toDTO()
    {
        return LocationUpsertDTO::fromRequest();
    }
}
