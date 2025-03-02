<?php

namespace App\Modules\EventTracking\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\EventTracking\Actions\EventViewAction;
use App\Modules\EventTracking\Transformers\EventViewTransformer;

class EventTrackingController extends ApiController
{
    public function view(EventViewAction $action, EventViewTransformer $transformer)
    {
        $data = $action->handle($this->request->all());

        return $this->response->paginator($data, $transformer);
    }
}
