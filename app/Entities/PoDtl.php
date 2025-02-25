<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class PoDtl extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    const STS_NEW = "NW";
    const STS_RECEIVING = "RG";
    const STS_RECEIVED = "RE";
    const STS_CANCEL = "CC";

    public $table = 'po_dtl';

    protected $primaryKey = 'po_dtl_id';

    protected $guarded = [
        'po_dtl_id',
    ];

    public function getStatusColumn()
    {
        return 'po_dtl_sts';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_TYPE_PO_DTL;
    }

    public function poHdr()
    {
        return $this->belongsTo(PoHdr::class, 'po_hdr_id');
    }

    public function grDtls()
    {
        return $this->hasMany(GrDtl::class, 'po_dtl_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public static function getDefaultLot()
    {
        return date('mY');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class, 'bin_loc_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function grLogs()
    {
        return $this->hasMany(GrLog::class, 'po_dtl_id');
    }
}
