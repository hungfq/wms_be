<?php

namespace App\Modules\User\Actions;

use App\Entities\Role;
use App\Entities\User;
use App\Libraries\Helpers;
use App\Modules\User\DTO\UserViewDTO;

class UserViewAction
{
    /**
     * @param $dto UserViewDTO
     */
    public function handle($dto)
    {
        $query = User::query()
            ->with('advisorTopics')
            ->with('criticalTopics')
            ->join('user_has_roles', 'user_has_roles.user_id', '=', 'users.id');

        $query->addSelect([
            $query->qualifyColumn('*'),
            'uc.name as created_by_name',
            'uu.name as updated_by_name',
        ]);

        $query->leftJoin('users as uc', 'uc.id', '=', $query->qualifyColumn('created_by'))
            ->leftJoin('users as uu', 'uu.id', '=', $query->qualifyColumn('updated_by'));

        if ($search = $dto->search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.code', 'LIKE', "%$search%")
                    ->orWhere('users.name', 'LIKE', "%$search%")
                    ->orWhere('users.email', 'LIKE', "%$search%");
            });
        }

        $ignoreIds = $dto->ignore_ids;
        if ($ignoreIds) {
            $query->orderByRaw("FIELD(users.id, " . implode(',', $ignoreIds) . ") desc");
        }


        if ($type = $dto->type) {
            $role = Role::where('name', $type)->first();
            $query->where('user_has_roles.role_id', data_get($role, 'id'));
        }

        if ($dto->is_active) {
            $query->where('users.status', User::STATUS_ACTIVE);
        }

        if ($dto->not_done_any_topic) {
            $query->whereDoesntHave('studentTopics', function ($q) use ($dto) {
                $q->where('lecturer_approved', '=', 1)
                    ->where('critical_approved', '=', 1);

                if ($dto->ignore_schedule_id) {
                    $q->where('schedule_id', '!=', $dto->ignore_schedule_id);
                }
            });
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',
        ]);

        if ($dto->limit) {
            return $query->paginate($dto->limit);
        }

        return $query->get();
    }
}