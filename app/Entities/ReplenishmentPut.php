<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class ReplenishmentPut extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    public $table = 'replenishment_puts';

    protected $guarded = [
        'id',
    ];

    public function replenishment()
    {
        return $this->belongsTo(Replenishment::class, 'repln_id');
    }

    public function summary()
    {
        return $this->belongsTo(ReplenishmentSummary::class, 'repln_summary_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id');
    }
}