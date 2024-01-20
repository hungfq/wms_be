<?php

namespace App\Entities;


class State extends BaseModel
{
    public $table ='states';

    protected $primaryKey = 'id';

    const STATUS_KEY = 'STATE_STATUS';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    protected $guarded = ['id'];

    public function country()
    {
        return $this->belongsTo(Country::class,'country_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
