<?php

namespace App\Modules\User\Actions;

use App\Entities\Role;
use App\Entities\User;
use App\Exceptions\UserException;
use App\Modules\User\DTO\UserStoreDTO;

class UserStoreAction
{
    public UserStoreDTO $dto;
    public $user;
    public $role;

    /***
     * @param UserStoreDTO $dto
     * @return void
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->createUser();
    }

    protected function checkData()
    {
        $isExists = User::where('email', $this->dto->email)
            ->exists();
        if ($isExists) {
            throw new UserException("Email đã tồn tại trong hệ thống!", 400);
        }

        $this->role = Role::where('name', $this->dto->type)->first();
        if (!$this->role) {
//            throw new UserException("Role is not exists!");
            throw new UserException("Vai trò không tồn tại trong hệ thống!", 400);
        }

        return $this;
    }

    protected function createUser()
    {
        $this->user = User::create($this->dto->all());
        $this->user->roles()->attach($this->role->id);
        $this->user->save();
        return $this;
    }
}
