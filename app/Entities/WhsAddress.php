<?php

namespace App\Entities;

class WhsAddress extends BaseSoftModel
{
    public $table ='whs_address';

    protected $primaryKey = 'addr_id';

    protected $guarded = ['id'];

    public function state()
    {
        return $this->belongsTo(State::class,'state_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'whs_id');
    }
}
