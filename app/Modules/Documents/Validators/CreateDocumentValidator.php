<?php

namespace App\Modules\Documents\Validators;

use App\Validators\AbstractValidator;

class CreateDocumentValidator extends AbstractValidator
{

    public function rules($params = [])
    {
        return [
            'owner' => ['required'],
            'title' => ['nullable'],
            'file' => [
                'required',
                'file',
                'max:100000'
            ],
        ];
    }
}
