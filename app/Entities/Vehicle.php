<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Vehicle extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    protected $primaryKey = "id";

    protected $guarded = [
        "id",
    ];

    public function getStatusColumn()
    {
        return 'status';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_VEHICLE_TYPE;
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }
}
