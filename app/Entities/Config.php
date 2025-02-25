<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = [
        'name',
        'code',
        'label',
        'value',
        'value_fe',
    ];
}
