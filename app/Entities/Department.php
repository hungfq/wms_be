<?php

namespace App\Entities;

class Department extends BaseModel
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'created_by',
        'updated_by'
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
