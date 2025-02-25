<?php

namespace App\Entities;

class GrLog extends BaseSoftModel
{
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'cartons_ids' => 'json'
    ];

    public function grHdr()
    {
        return $this->belongsTo(GrHdr::class, 'gr_hdr_id');
    }

    public function grDtl()
    {
        return $this->belongsTo(GrDtl::class, 'gr_dtl_id');
    }

    public function poHdr()
    {
        return $this->belongsTo(PoHdr::class, 'po_hdr_id');
    }

    public function poDtl()
    {
        return $this->belongsTo(PoDtl::class, 'po_dtl_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id');
    }
}