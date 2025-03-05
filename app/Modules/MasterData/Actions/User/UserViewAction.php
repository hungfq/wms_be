<?php

namespace App\Modules\MasterData\Actions\User;

use App\Entities\User;
use App\Libraries\Helpers;
use App\Modules\MasterData\DTO\User\UserViewDTO;

class UserViewAction
{
    public UserViewDTO $dto;

    /**
     * @param UserViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = User::query()
            ->select([
                'users.*',
            ])
            ->join('profiles', 'profiles.user_id', '=', 'users.id');

        if ($this->dto->email) {
            $query->where('users.email', 'LIKE', "%{$this->dto->email}%");
        }

        if ($this->dto->user_name) {
            $query->where('users.name', 'LIKE', "%{$this->dto->user_name}%");
        }

        if ($this->dto->status) {
            $query->where('users.name', '=', $this->dto->status);
        }

        if ($this->dto->first_name) {
            $query->where('profiles.first_name', 'LIKE', "%{$this->dto->first_name}%");
        }

        if ($this->dto->last_name) {
            $query->where('profiles.last_name', 'LIKE', "%{$this->dto->last_name}%");
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'abc' => 'profiles.abc',
        ]);

        return $query->paginate(data_get($dto, 'limit', ITEM_PER_PAGE));
    }
}
