<?php

namespace App\Listeners;

use App\Events\SendEmailEvent;
use App\Jobs\SendEmailJob;

class SendEmailListener
{
    public function handle(SendEmailEvent $event)
    {
        if (empty($event->data)) {
            return;
        }

        $dataTransfer = $event->data;

        dispatch(new SendEmailJob([
            'data' => $dataTransfer
        ]));
    }
}