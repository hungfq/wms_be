<?php

namespace App\Modules\User\Actions;

use App\Entities\User;
use App\Exceptions\UserException;

class UserDeleteAction
{

    /***
     * @return void
     */
    public function handle($id)
    {
        $user = User::find($id);
        if (!$user) {
//            throw new UserException("User is not exists!");
            throw new UserException('Người dùng không tồn tại trong hệ thống!', 400);
        }

        $user->delete();
    }
}
