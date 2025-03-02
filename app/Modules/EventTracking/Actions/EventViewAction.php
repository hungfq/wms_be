<?php

namespace App\Modules\EventTracking\Actions;

use App\Entities\EventLog;

class EventViewAction
{
    public function handle($params)
    {
        $query = EventLog::query()
            ->with([
                'createdBy',
            ])
            ->select([
                'event_logs.*',
            ]);

        if (isset($params['whs_id'])) {
            $query->where('event_logs.whs_id', $params['whs_id']);
        }

        if (isset($params['owner'])) {
            $query->where('event_logs.owner', $params['owner']);
        }

        return $query->paginate(data_get($params, 'limit'), ITEM_PER_PAGE);
    }
}