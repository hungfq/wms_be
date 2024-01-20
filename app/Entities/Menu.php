<?php

namespace App\Entities;


class Menu extends BaseModel
{
    public $table = 'menu';

    protected $fillable = [
        'content'
    ];

    protected $casts = [
        'content' => 'json'
    ];
}
