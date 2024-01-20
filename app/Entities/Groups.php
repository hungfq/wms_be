<?php

namespace App\Entities;

class Groups extends BaseSoftModel
{
    const STATUS_KEY = 'GROUP_STATUS';

    const TYPE_ITEM_LOCATION = 'ITEM_LOCATION';
    const TYPE_CHANNEL = 'CHANNEL';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    protected $primaryKey = 'id';

    protected $guarded = [
        'id',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
