<?php

namespace App\Entities;

class OdrOutSort extends BaseSoftModel
{
    const IS_DAMAGE_FALSE = 0;
    const IS_DAMAGE_TRUE = 1;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'odr_carton_ids' => 'json'
    ];

    public function odrHdr()
    {
        return $this->belongsTo(OrderHdr::class, 'odr_hdr_id', 'id');
    }

    public function odrDetail()
    {
        return $this->belongsTo(Pallet::class, 'odr_dtl_id', 'id');
    }

    public function odrCarton()
    {
        return $this->belongsTo(OdrCarton::class, 'odr_carton_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
