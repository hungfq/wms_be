<?php

namespace App\Libraries;

use ElephantIO\Client;
use Illuminate\Support\Facades\Log;

class Socket
{
    public static function sendUpdateTaskRequest($ids, $topicId)
    {
        try {
            $client = new Client(Client::engine(Client::CLIENT_3X, env('SOCKET_URL', '')));
            $client->initialize();
            $client->of('/');
            foreach ($ids as $id) {
                $client->emit('update-task', [
                    'id' => $id,
                    'topicId' => $topicId,
                ]);
            }
            $client->close();
        } catch (\Exception $e) {
            Log::info($e);
        }
    }

    public static function sendUpdateNotificationRequest($ids, $notification)
    {
        try {
            $client = new Client(Client::engine(Client::CLIENT_3X, env('SOCKET_URL', '')));
            $client->initialize();
            $client->of('/');
            foreach ($ids as $id) {
                $client->emit('update-notify', [
                    'id' => $id,
                    'notification' => $notification,
                ]);
            }
            $client->close();
        } catch (\Exception $e) {
            Log::info($e);
        }

    }

    public static function sendUpdateTaskInfoRequest($ids, $taskId)
    {
        try {
            $client = new Client(Client::engine(Client::CLIENT_3X, env('SOCKET_URL', '')));
            $client->initialize();
            $client->of('/');
            foreach ($ids as $id) {
                $client->emit('update-task-info', [
                    'id' => $id,
                    'taskId' => $taskId,
                ]);
            }
            $client->close();
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}