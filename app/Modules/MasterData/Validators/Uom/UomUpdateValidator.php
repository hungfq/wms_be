<?php

namespace App\Modules\MasterData\Validators\Uom;

use App\Modules\MasterData\DTO\Uom\UomUpsertDTO;
use App\Validators\AbstractValidator;

class UomUpdateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'id' => 'required',
            'code' => 'required',
            'name' => 'required',
        ];
    }


    public function toDTO()
    {
        return UomUpsertDTO::fromRequest();
    }
}
