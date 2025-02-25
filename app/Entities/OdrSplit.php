<?php

namespace App\Entities;

class OdrSplit extends BaseSoftModel
{
    protected $guarded = [
        'id',
    ];

    public function orderParent()
    {
        return $this->belongsTo(OrderHdr::class, 'odr_hdr_id');
    }

    public function orderSplit()
    {
        return $this->belongsTo(OrderHdr::class, 'odr_split_id', 'odr_hdr_id');
    }
}
