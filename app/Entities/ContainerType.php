<?php

namespace App\Entities;

class ContainerType extends BaseSoftModel
{
    protected $guarded = ['id'];

    const STATUS_KEY = 'CONTAINER_TYPE_STATUS';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
