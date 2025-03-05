<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class LocationType extends BaseSoftModel implements StatusRelationshipInterface
{
    use StatusesRelationshipTrait;
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    protected $primaryKey = 'loc_type_id';
    protected $table = 'loc_types';

    protected $guarded = [
        'loc_type_id',
    ];


    public function getStatusColumn()
    {
        return 'status';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_TYPE_LOC_TYPE;
    }
}