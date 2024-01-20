<?php

namespace App\Entities;

class ItemGroup extends BaseSoftModel
{
    protected $guarded = ['id'];

    const STATUS_KEY = 'ITEM_GROUP_STATUSES';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    public function items()
    {
        return $this->hasMany(Item::class, 'group_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}