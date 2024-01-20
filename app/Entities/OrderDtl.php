<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class OrderDtl extends BaseSoftModel implements StatusRelationshipInterface
{
    use StatusesRelationshipTrait;
//    use WarehouseRelationshipBelongToTrait;
//    use CustomerRelationshipBelongToTrait;
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    const STATUS_TYPE = 'ORDER_DTL_STS';
    const STS_NEW = 'NW';
    const STS_ALLOCATE = 'AL';
    const STS_CANCEL = 'CC';
    const STS_PICKING = 'PK';
    const STS_PICKED = 'PD';
    const STS_PACKING = 'PN';
    const STS_PACKED = 'PA';
    const STS_STAGING = 'ST';
    const STS_READY_TO_SHIP = 'RS';
    const STS_SCHEDULED_TO_SHIP = 'SS';
    const STS_SHIPPED = 'SH';
    const STS_OUT_SORTING = 'OS';
    const STS_OUT_SORTED = 'OD';
    const STS_PENDING = 'PG';

    const IS_CONFIRM_YES = 1;
    const IS_CONFIRM_NO = 0;
    const ALLOCATE_DEFAULT = 0;
    const ALLOCATE_RETAIL = 1;
    const ALLOCATE_WHOLESALE = 2;

    public $table = 'odr_dtl';

    protected $guarded = [
        'id',
    ];

    public $columnDefaultCalcM3 = 'IF(odr_dtl.ctn_ttl, odr_dtl.ctn_ttl, CEIL(odr_dtl.cancelled_qty / items.pack_size))';

    public function getStatusColumn()
    {
        return 'odr_dtl_sts';
    }

    public function getStatusTypeValue()
    {
        return self::STATUS_TYPE;
    }

    public function orderCartons()
    {
        return $this->hasMany(OdrCarton::class, 'odr_dtl_id');
    }

    public function orderOutSorts()
    {
        return $this->hasMany(OdrOutSort::class, 'odr_dtl_id');
    }

    public function OdrOutSortLogs()
    {
        return $this->hasMany(OdrOutSortLog::class, 'odr_dtl_id');
    }

    public function order()
    {
        return $this->belongsTo(OrderHdr::class, 'odr_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function getInventory()
    {
        return $this->belongsTo(Inventory::class, 'item_id', 'item_id')
            ->where('lot', $this->lot)
            ->where('cus_id', $this->cus_id)
            ->where('bin_loc_id', $this->bin_loc_id)
            ->where('whs_id', $this->whs_id);
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class, 'bin_loc_id');
    }

    public function orderParentOutSorts()
    {
        return $this->hasMany(OdrOutSort::class, 'odr_dtl_id', 'parent_id');
    }

    public function orderDrops()
    {
        return $this->hasMany(OdrDrop::class, 'odr_dtl_id', 'id');
    }
}
