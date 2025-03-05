<?php

namespace App\Modules\MasterData\Actions\User;

use App\Entities\CustomerInUser;
use App\Entities\Profile;
use App\Entities\Role;
use App\Entities\User;
use App\Modules\MasterData\Controllers\UserController;
use App\Modules\MasterData\DTO\User\UserStoreDTO;
use Illuminate\Support\Facades\Hash;

class UserStoreAction
{
    public UserStoreDTO $dto;
    public $user;

    /**
     * @param UserStoreDTO $dto
     */

    public function handle($dto)
    {
        $this->dto = $dto;

        $this->createUser()
            ->syncCusAndWhs();
    }

    public function createUser()
    {
        $this->user = User::query()
            ->create([
                'email' => $this->dto->email,
                'name' => $this->dto->name,
                'status' => $this->dto->status,
                'password' => Hash::make($this->dto->password),
            ]);

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
