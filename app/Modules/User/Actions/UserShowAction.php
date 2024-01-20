<?php

namespace App\Modules\User\Actions;

use App\Entities\User;

class UserShowAction
{
    public function handle($id)
    {
        $query = User::query()
            ->where('users.id', $id)
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


        return $query->first();
    }
}