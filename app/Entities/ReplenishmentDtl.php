<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class ReplenishmentDtl extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    public $table = 'replenishment_dtls';

    protected $guarded = [
        'id',
    ];

    const STATUS_KEY = 'REPLENISHMENT_DETAIL_STATUSES';
    const STATUS_NEW = 'NW';
    const STATUS_PICKED = 'PD';
    const STATUS_REPLENISHING = 'RG';
    const STATUS_REPLENISHED = 'RE';
    const STATUS_CANCELED = 'CC';

    public function replenishment()
    {
        return $this->belongsTo(Replenishment::class, 'repln_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

}