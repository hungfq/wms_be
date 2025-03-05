<?php

namespace App\Entities;


use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Zone extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    const STATUS_TYPE = 'ZONE_STS';
    public $table = 'zones';

    protected $primaryKey = 'zone_id';

    protected $guarded = [
        'zone_id',
    ];

    public function getStatusColumn()
    {
        return 'zone_sts';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_TYPE_ZONE;
    }

    public function zoneType()
    {
        return $this->belongsTo(ZoneType::class, 'zone_type_id');
    }
}