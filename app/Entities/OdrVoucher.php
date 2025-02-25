<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class OdrVoucher extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    protected $guarded = [
        'id',
    ];

    public function order()
    {
        return $this->belongsTo(OrderHdr::class, 'odr_hdr_id');
    }
}
