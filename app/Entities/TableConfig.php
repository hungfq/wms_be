<?php

namespace App\Entities;

class TableConfig extends BaseModel
{
    protected $fillable = [
        'user_id',
        'table_name',
        'value'
    ];

    protected $casts = [
        'value' => 'json'
    ];
}
