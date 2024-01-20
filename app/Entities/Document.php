<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Document extends BaseSoftModel
{
    use CreatedByRelationshipTrait, UpdatedByRelationshipTrait;

    protected $table = 'documents';

    protected $guarded = ['id'];
}