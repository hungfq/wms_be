<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class ConfigApply extends BaseModel
{
    protected $fillable = [
        'config_id',
        'created_by',
        'updated_by'
    ];

    public function config()
    {
        return $this->belongsTo(Config::class);
    }
}
