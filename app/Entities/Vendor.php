<?php

namespace App\Entities;

class Vendor extends BaseSoftModel
{
    protected $guarded = ['id'];

    const STATUS_KEY = 'VENDOR_STATUSES';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
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