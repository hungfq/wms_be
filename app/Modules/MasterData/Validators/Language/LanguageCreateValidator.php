<?php

namespace App\Modules\MasterData\Validators\Language;

use App\Validators\AbstractValidator;

class LanguageCreateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'message' => 'required',
            'language_code' => 'required',
        ];
    }
}
