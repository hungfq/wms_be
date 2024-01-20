<?php

namespace App\Entities;

use App\Traits\HasImageTrait;

class ItemImage extends BaseSoftModel
{
    use HasImageTrait;

    const UPLOAD_DIR = 'item-master';

    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFullPathAttribute()
    {
        return $this->path;
    }
}