<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class PoType extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    protected $guarded = [
        'id',
    ];

    const TYPE_RETURN = 'RTG';
    const TYPE_IMPORT = 'IMP';
    const TYPE_TRANSFER = 'TFI';
    const TYPE_CYCLE_COUNT = 'CCP';
    const TYPE_CUSTOMER_REJECT = 'CRJ';
}
