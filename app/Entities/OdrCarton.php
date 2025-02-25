<?php

namespace App\Entities;

class OdrCarton extends BaseSoftModel
{
    public $table = 'odr_carton';

    protected $guarded = [
        'id',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id', 'loc_id');
    }

    public function pallet()
    {
        return $this->belongsTo(Pallet::class, 'plt_id', 'plt_id');
    }

    public function carton()
    {
        return $this->belongsTo(Carton::class, 'ctn_id', 'ctn_id');
    }

    public function wvDtl()
    {
        return $this->belongsTo(WvDtl::class, 'wv_dtl_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class, 'bin_loc_id');
    }

    public function orderOutSorts()
    {
        return $this->hasMany(OdrOutSort::class, 'odr_carton_id');
    }
}
