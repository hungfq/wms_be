<?php

namespace App\Entities\Traits;

use App\Entities\User;

trait CreatedByRelationshipTrait
{
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}