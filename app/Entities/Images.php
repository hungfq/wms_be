<?php

namespace App\Entities;

class Images extends BaseSoftModel
{
    const TYPE_ASN = 'ASN';

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

    public function imageAbles()
    {
        return $this->hasMany(ImageAbles::class, 'images_id', 'id');
    }
}
