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


        Helpers::sortBuilder($query, $dto->toArray(), [
            'abc' => 'profiles.abc',
        ]);

        return $query->paginate(data_get($dto, 'limit', ITEM_PER_PAGE));
    }
}
