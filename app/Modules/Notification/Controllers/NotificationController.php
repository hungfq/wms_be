<?php

namespace App\Modules\Notification\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Notification\Actions\NotificationDeleteAction;
use App\Modules\Notification\Actions\NotificationReadAction;
use App\Modules\Notification\Actions\NotificationViewAction;
use App\Modules\Notification\DTO\NotificationViewDTO;
use App\Modules\Notification\Transformers\NotificationViewTransformer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationController extends ApiController
{
    public function view(NotificationViewAction $action, NotificationViewTransformer $transformer)
    {
        $results = $action->handle(NotificationViewDTO::fromRequest());

        if ($results instanceof Collection) {
            return $this->response->collection($results, $transformer);
        }

        return $this->response->paginator($results, $transformer);
    }

    public function read($id, NotificationReadAction $action)
    {
        DB::transaction(function () use ($action, $id) {
            $action->handle($id);
        });

        return $this->responseSuccess();
    }

    public function delete($id, NotificationDeleteAction $action)
    {
        DB::transaction(function () use ($action, $id) {
            $action->handle($id);
        });

        return $this->responseSuccess();
    }
}