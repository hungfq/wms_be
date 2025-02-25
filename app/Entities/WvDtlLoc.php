<?php

namespace App\Entities;

class WvDtlLoc extends BaseSoftModel
{
    protected $guarded = [
        'id',
    ];

    public $columnDefaultCalcM3 = 'CEIL(wv_dtl_locs.picked_qty / items.pack_size)';

    public function wvHdr()
    {
        return $this->belongsTo(WvHdr::class, 'wv_hdr_id');
    }

    public function wvDtl()
    {
        return $this->belongsTo(WvDtl::class, 'wv_dtl_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
