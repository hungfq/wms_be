<?php

namespace App\Modules\MasterData\Validators\User;

use App\Modules\MasterData\DTO\User\UserStoreDTO;
use App\Validators\AbstractValidator;

class UserStoreValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|unique:users,name',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
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
        return UserStoreDTO::fromRequest();
    }
}
