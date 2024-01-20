<?php

namespace App\Entities;

class PalletPrefix extends BaseSoftModel
{
    protected $table = 'pallet_prefixes';

    protected $guarded = [
        'id'
    ];

    const STATUS_KEY = 'PALLET_PREFIX_STATUS';

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
