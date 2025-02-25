<?php

namespace App\Entities\Traits;

use App\Entities\User;

trait UpdatedByRelationshipTrait
{
    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}