<?php

namespace App\Modules\Notification\Actions;

use App\Entities\Notification;
use App\Exceptions\UserException;
use Illuminate\Support\Facades\Auth;

class NotificationDeleteAction
{
    public function handle($id)
    {
        $notify = Notification::query()
            ->where('id', $id)
            ->where('to_id', Auth::id())
            ->first();

        if (!$notify) {
//            throw new UserException('Notification is not exist');
            throw new UserException('Thông báo không tồn tại!', 400);
        }

        $notify->delete();
    }
}