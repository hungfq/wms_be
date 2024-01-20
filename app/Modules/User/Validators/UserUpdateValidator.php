<?php

namespace App\Modules\User\Validators;

use App\Entities\User;
use App\Modules\User\DTO\UserUpdateDTO;
use App\Validators\AbstractValidator;
use Illuminate\Validation\Rule;

class UserUpdateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'id' => 'required',
            'email' => 'required|string',
            'code' => 'required|string',
            'name' => 'required|string',
            'gender' => 'nullable',
            'picture' => 'nullable',
            'status' => 'nullable|' . Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE]),
        ];
    }

    public function toDTO()
    {
        return UserUpdateDTO::fromRequest();
    }
}
