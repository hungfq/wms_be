<?php

namespace App\Entities\Traits;


use App\Entities\Statuses;

trait StatusesRelationshipTrait
{
    public function statuses()
    {
        $statusColumn = $this->getStatusColumn();
        $statusTypeValue = $this->getStatusTypeValue();;

        return $this->belongsTo(
            Statuses::class,
            $statusColumn,
            'sts_code'
        )
            ->where('sts_type', '=', $statusTypeValue);
    }

}
