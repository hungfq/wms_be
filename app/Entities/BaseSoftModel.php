<?php

namespace App\Entities;

use App\Databases\Eloquent\SoftDeletes;

class BaseSoftModel extends BaseModel
{
    use SoftDeletes;

    const DELETED_AT = 'deleted_at';

    const DEFAULT_STS_ACTIVE = 'AC';
    const DEFAULT_STS_INACTIVE = 'IA';

    protected static function boot()
    {
        parent::boot();
    }

}