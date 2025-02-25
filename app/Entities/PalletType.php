<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class PalletType extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    const STATUS_KEY = 'PALLET_TYPE_STATUS';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    protected $table = 'pallet_types';

    protected $primaryKey = 'id';

    protected $guarded = [
        'id',
    ];
}