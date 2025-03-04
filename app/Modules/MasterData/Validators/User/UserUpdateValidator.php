<?php

namespace App\Modules\MasterData\Validators\User;

use App\Modules\MasterData\DTO\User\UserUpdateDTO;
use App\Validators\AbstractValidator;

class UserUpdateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'email' => 'required|email',
            'name' => 'required',
            'password' => 'nullable|min:6',
            'confirm_password' => 'nullable|same:password',
            'status' => 'required|in:AC,IA',
            'role_ids' => 'required|array',
            'role_ids.*' => 'required|integer',
            'profile' => 'required|array',
            'profile.first_name' => 'required',
            'profile.last_name' => 'required',
            'profile.full_name' => 'nullable',
            'profile.gender' => 'required|in:MALE,FEMALE',
            'profile.contact_email' => 'nullable|email',
            'profile.contact_phone' => 'nullable|min:10',
            'profile.department_id' => 'nullable|integer',
            'profile.code' => 'nullable',
            'profile.location' => 'nullable',
            'user_warehouse' => 'required|array',
        ];
    }


    public function toDTO()
    {
        return UserUpdateDTO::fromRequest();
    }
}
