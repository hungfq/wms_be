<?php

namespace App\Modules\MasterData\Validators\ReplenishmentConfig;

use App\Modules\MasterData\DTO\ReplenishmentConfig\ReplenishmentConfigCreateDTO;
use App\Validators\AbstractValidator;

class ReplenishmentConfigCreateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'whs_id' => 'required|integer',
            'item_id' => 'required|integer',
            'min_qty' => 'required|numeric',
            'des' => 'nullable|string',
        ];
    }

    public function messages($params = [])
    {
        return [
        ];
    }

    public function toDTO()
    {
        return ReplenishmentConfigCreateDTO::fromRequest();
    }
}
