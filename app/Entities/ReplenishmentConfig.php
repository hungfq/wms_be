<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class ReplenishmentConfig extends BaseSoftModel
{
//    use WarehouseRelationshipBelongToTrait;
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    public $table = 'replenishment_configs';

    protected $guarded = [
        'id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

}