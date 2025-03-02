<?php

namespace App\Entities;

use Illuminate\Support\Facades\DB;

class WvHdr extends BaseSoftModel
{
    const STS_NEW = 'NW';
    const STS_PICKING = 'PK';
    const STS_PICKED = 'PD';
    const STS_CANCEL = 'CC';

    protected $fillable = [
        'whs_id',
        'picker_id',
        'wv_hdr_sts',
        'order_cancelled',
        'wv_hdr_num',
        'is_pick_mb',
    ];

    public function pickers()
    {
        return $this->belongsToMany(User::class, 'wv_has_picker', 'wv_hdr_id', 'picker_id');
    }

    public function wvDtls()
    {
        return $this->hasMany(WvDtl::class);
    }

    public function odrHdrs()
    {
        return $this->hasMany(OrderHdr::class, 'wv_id');
    }

    public function odrCartons()
    {
        return $this->hasMany(OdrCarton::class, 'wv_hdr_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function wvDtlLocs()
    {
        return $this->hasMany(WvDtlLoc::class, 'wv_hdr_id');
    }

    public static function generateWvHdrNum()
    {
        $currentYearMonth = date('ym');
        $defaultNum = "WV-${currentYearMonth}-000001";
        $wvHdr = DB::table(WvHdr::getTableName())
            ->orderBy('id', 'desc')
            ->first();

        if (!$wvHdr) {
            return $defaultNum;
        }

        [$prefix, $yearMonth, $lastNum] = explode('-', $wvHdr->wv_hdr_num);
        if ($currentYearMonth != $yearMonth) {
            return $defaultNum;
        }

        return sprintf('%s-%s-%06d', $prefix, $currentYearMonth, ++$lastNum);
    }
}
