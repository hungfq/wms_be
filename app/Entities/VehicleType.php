<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class VehicleType extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    protected $primaryKey = "id";

    protected $guarded = [
        "id",
    ];
}
