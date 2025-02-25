<?php

namespace App\Entities;

class Container extends BaseSoftModel
{
    public $table = 'containers';

    protected $primaryKey = 'ctnr_id';

    protected $fillable = [
        'code',
        'des',
        'is_auto',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_auto' => 'boolean'
    ];

    public function grHdr()
    {
        return $this->hasOne(GrHdr::class, 'ctnr_id')
            ->where('gr_hdr_sts', 'RG');
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
