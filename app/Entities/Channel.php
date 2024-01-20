<?php

namespace App\Entities;

class Channel extends BaseSoftModel
{
    protected $guarded = ['id'];

    const STATUS_KEY = 'CHANNEL_STATUSES';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

//    public function thirdPartyGroups()
//    {
//        return $this->hasMany(ThirdPartyGroup::class, 'channel_id');
//    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
