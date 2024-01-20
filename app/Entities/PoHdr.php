<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;
use Illuminate\Support\Facades\DB;

class PoHdr extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
//    use WarehouseRelationshipBelongToTrait;
//    use CustomerRelationshipBelongToTrait;
    use StatusesRelationshipTrait;

    const STS_NEW = "NW";
    const STS_RECEIVING = "RG";
    const STS_RECEIVED = "RE";
    const STS_CANCEL = "CC";

    const PO_RMA = 'RMA';
    const PO_WMS = 'WMS';
    const PO_TRANSFER = 'TRANSFER';

    const FOLDER_ASN = 'asn';

    const CODE_UPLOAD_IMAGE_SEAL = 'seal';
    const CODE_UPLOAD_IMAGE_CONTAINER_NUM = 'ctn_num';
    const CODE_UPLOAD_IMAGE_CONTAINER_DOOR = 'ctn_door';
    const CODE_UPLOAD_IMAGE_EMPTY_CONTAINER = 'empty_ctnr';

    public $table = 'po_hdr';

    protected $primaryKey = 'po_hdr_id';

    protected static $lastNum;

    protected $guarded = [
        'po_hdr_id',
    ];

    public function getStatusColumn()
    {
        return 'po_sts';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_TYPE_PO;
    }

    public function poDtls()
    {
        return $this->hasMany(PoDtl::class, 'po_hdr_id');
    }

    public function grHdrs()
    {
        return $this->hasMany(GrHdr::class, 'po_hdr_id');
    }

    public function type()
    {
        return $this->belongsTo(PoType::class, 'po_type', 'code');
    }

    public function doHdr()
    {
        return $this->belongsTo(OrderHdr::class, 'odr_hdr_id');
    }

    public function fromWareHouses()
    {
        return $this->belongsTo(Warehouse::class, 'from_whs_id', 'whs_id');
    }

    public function toWareHouses()
    {
        return $this->belongsTo(Warehouse::class, 'to_whs_id', 'whs_id');
    }

    public static function generateNum()
    {
        if ( self::$lastNum ) {
            self::$lastNum++;
            return self::$lastNum;
        }

        $currentYearMonth = date('ym');
        $defaultNum = 'ASN' . "-${currentYearMonth}-000001";
        $poHdr = DB::table('po_hdr')
            ->select('po_num')
            ->where('po_num', 'LIKE', "ASN-{$currentYearMonth}-%")
            ->orderBy('po_num', 'DESC')
            ->first();

        $lastNum = data_get($poHdr, 'po_num');

        if ( !$lastNum || strpos($lastNum, "-${currentYearMonth}-") === false) {
            self::$lastNum = $defaultNum;
            return self::$lastNum;
        }

        self::$lastNum = ++$lastNum;
        return self::$lastNum;
    }

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class);
    }

    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id', 'ctnr_id');
    }

    public function fromVendor()
    {
        return $this->belongsTo(Vendor::class, 'from_vendor_id');
    }

    public function images()
    {
        return $this->morphToMany(Images::class, 'imageables');
    }
}
