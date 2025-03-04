<?php

namespace App\Modules\EventTracking\Transformers;

use App\Libraries\Language;
use League\Fractal\TransformerAbstract;

class EventViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        $params = data_get($model, 'info_params', []);

        return [
            'owner' => data_get($model, 'owner'),
            'transaction' => data_get($model, 'owner'),
            'event_code' => data_get($model, 'event_code'),
            'info' => Language::translate(data_get($model, 'info'), ...$params),
            'created_at' => data_get($model, 'created_at'),
            'created_by_name' => data_get($model, 'createdBy.name'),
        ];
    }
}
