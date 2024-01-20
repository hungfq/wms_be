<?php

namespace App\Entities;

class OdrHdrTPAddition extends BaseSoftModel
{
    public $table = 'odr_hdr_tp_additions';

    protected $guarded = ['id'];

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class, 'tp_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'ship_to_state');
    }
}