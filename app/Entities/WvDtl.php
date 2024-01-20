<?php

namespace App\Entities;

class WvDtl extends BaseSoftModel
{
    const STS_NEW = 'NW';
    const STS_PICKING = 'PK';
    const STS_PICKED = 'PD';
    const STS_CANCEL = 'CC';

    const IS_CONFIRM_YES = 1;
    const IS_CONFIRM_NO = 0;

    protected $fillable = [
        'whs_id',
        'cus_id',
        'wv_hdr_id',
        'picker_id',
        'wv_dtl_sts',
        'item_id',
        'lot',
        'piece_qty',
        'picked_qty',
        'put_back_qty',
        'cancelled_qty',
        'algorithm',
        'bin_loc_id'
    ];

    public $columnDefaultCalcM3 = 'CEIL(wv_dtl_locs.picked_qty / items.pack_size)';

    public function wvHdr()
    {
        return $this->belongsTo(WvHdr::class);
    }

    public function picker()
    {
        return $this->belongsTo(User::class, 'picker_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function cartons()
    {
        return $this->hasMany(Carton::class, 'item_id', 'item_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cus_id', 'cus_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function odrCartons()
    {
        return $this->hasMany(OdrCarton::class, 'wv_dtl_id');
    }

    public function odrHdrs()
    {
        return $this->belongsToMany(OrderHdr::class, 'odr_carton', 'wv_dtl_id', 'odr_hdr_id');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class, 'bin_loc_id');
    }

    public function wvDtlLocs()
    {
        return $this->hasMany(WvDtlLoc::class, 'wv_dtl_id');
    }
}
