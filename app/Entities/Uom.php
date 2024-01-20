<?php

namespace App\Entities;


use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Uom extends BaseModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    public $table = 'uoms';

    protected $fillable = [
        'code',
        'name',
        'description',
        'created_by',
        'updated_by'
    ];
}
