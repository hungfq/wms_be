<?php

namespace App\Modules\MasterData\Validators\Uom;

use App\Modules\MasterData\DTO\Uom\UomUpsertDTO;
use App\Validators\AbstractValidator;

class UomStoreValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'code' => 'required',
            'name' => 'required',
        ];
    }


    public function toDTO()
    {
        return UomUpsertDTO::fromRequest();
    }
}
