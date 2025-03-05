<?php

namespace App\Modules\MasterData\Actions\User;

use App\Entities\CustomerInUser;
use App\Entities\Profile;
use App\Entities\Role;
use App\Entities\User;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\Controllers\UserController;
use App\Modules\MasterData\DTO\User\UserUpdateDTO;
use Illuminate\Support\Facades\Hash;

class UserUpdateAction
{
    public UserUpdateDTO $dto;
    public $user;

    /**
     * @param UserUpdateDTO $dto
     */

    public function handle($dto)
    {
        $this->dto = $dto;

        $this->updateUser()
            ->syncCusAndWhs();
    }

    public function updateUser()
    {
        $this->user = User::query()->find($this->dto->id);
        if (!$this->user) {
            throw  new UserException(Language::translate('User not found.'));
        }

        $userAttributes = [
            'email' => $this->dto->email,
            'name' => $this->dto->name,
            'status' => $this->dto->status,
        ];
        if ($this->dto->password) {
            $userAttributes['password'] = Hash::make($this->dto->password);
        }
        $this->user->fill($userAttributes);
        $this->user->save();

        $profileAttributes = $this->dto->profile;
        $profileAttributes['full_name'] = data_get($profileAttributes, 'first_name') . ' ' . data_get($profileAttributes, 'last_name');
        Profile::updateOrCreate(
            ['user_id' => $this->user->id],
            $profileAttributes
        );

        $roles = Role::query()->whereIn('id', $this->dto->role_ids)->get();
        $this->user->syncRoles($roles);

        return $this;
    }

    public function syncCusAndWhs()
    {
        $userWarehouse = $this->dto->user_warehouse;
        $whsIds = [];
        CustomerInUser::query()->where('user_id', $this->user->id)->delete();
        foreach ($userWarehouse as $warehouse) {
            $whsIds[] = data_get($warehouse, 'whs_id');
            CustomerInUser::query()->insert([
                'cus_id' => UserController::DEFAULT_CUS_ID,
                'whs_id' => data_get($warehouse, 'whs_id'),
                'user_id' => $this->user->id,
            ]);
        }

        $this->user->warehouses()->sync($whsIds);

        return $this;
    }
}
