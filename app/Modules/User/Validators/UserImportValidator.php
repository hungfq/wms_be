<?php

namespace App\Modules\User\Validators;

use App\Modules\User\DTO\UserImportDTO;
use App\Validators\AbstractValidator;

class UserImportValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'type' => 'required|string',
            'file' => [
                'file',
                'mimes:xls,xlsx',
                'required',
            ],
        ];
    }

    public function toDTO()
    {
        return UserImportDTO::fromRequest();
    }
}
