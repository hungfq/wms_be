<?php

namespace App\Entities\Observer;

use Illuminate\Support\Facades\Auth;

class EventDBObserver
{

    /**
     * Handle to the Model "creating" event.
     *
     * @param $model
     */
    public function creating($model)
    {
        $model->created_by = $model->updated_by = Auth::id();

        return true;
    }

    /**
     * Handle to the Model "updating" event.
     *
     * @param $model
     */
    public function updating($model)
    {
        $model->updated_by = Auth::id();

        return true;
    }

    /**
     * Handle to the Model "updating" event.
     *
     * @param $model
     */
    public function created($model)
    {
        return true;
    }

}

