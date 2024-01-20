<?php

namespace App\Entities;

class BinLocation extends BaseSoftModel
{
    protected $fillable = [
        'whs_id',
        'name',
        'code',
        'description',
        'status_code',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted'
    ];

    const STATUS_KEY = 'BIN_LOCATION_STATUS';

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
