<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderHdr extends BaseSoftModel implements StatusRelationshipInterface
{
//    use WarehouseRelationshipBelongToTrait;
//    use CustomerRelationshipBelongToTrait;
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    const STATUS_KEY = 'ORDER_STATUS';
    const DELIVERY_TO_KEY = 'ORDER_DELIVERY_TO';
    const STATUS_TYPE = 'ORDER_STS';

    const STS_NEW = 'NW';
    const STS_ALLOCATED = 'AL';
    const STS_PICKING = 'PK';
    const STS_PICKED = 'PD';
    const STS_PACKING = 'PN';
    const STS_PACKED = 'PA';
    const STS_STAGING = 'ST';
    const STS_READY_TO_SHIP = 'RS';
    const STS_SHIPPED = 'SH';
    const STS_CANCELED = 'CC';
    const STS_SCHEDULED_TO_SHIP = 'SS';
    const STS_OUT_SORTING = 'OS';
    const STS_OUT_SORTED = 'OD';
    const STS_PENDING = 'PG';

    const TYPE_BULK = 'BU';
    const TYPE_TRANSFER = 'TF';
    const TYPE_BACK_ORDER = 'BAC';
    const TYPE_RMA = 'RMA';
    const TYPE_CRS_DOCK = 'CDK';
    const TYPE_CYCLE_COUNT = 'CCP';
    const TYPE_NISSIN_BACK_ORDER = 'NBA';
    const TYPE_NISSIN_DROP_ORDER = 'DRP';

    const COMBINE_KEY = 'ORDER_COMBINE';
    const COMBINE_TYPE_ORIGIN = 'OG';
    const COMBINE_TYPE_COMBINED = 'CD';

    const PREFIX_ORDER_NUM = 'ORD';
    const ORDER_NUM_CACHE_KEY = 'order_num';

    public $table = 'odr_hdr';

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'data_split' => 'json'
    ];

    public $columnDefaultCalcM3 = 'odr_dtl.ctn_ttl';

    public function orderDtls()
    {
        return $this->hasMany(OrderDtl::class, 'odr_id');
    }

    public function wvHdr()
    {
        return $this->belongsTo(WvHdr::class, 'wv_id', 'id');
    }

    public function transferWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'transfer_whs_id', 'whs_id');
    }

    public function getStatusColumn()
    {
        return 'odr_sts';
    }

    public function getStatusTypeValue()
    {
        return self::STATUS_TYPE;
    }

    public static function orderType()
    {
        return [
            self::TYPE_BULK => 'Bulk',
            self::TYPE_TRANSFER => 'Transfer',
            self::TYPE_RMA => 'RMA',
            self::TYPE_CRS_DOCK => 'Crossdock',
            self::TYPE_CYCLE_COUNT => 'Cycle Count',
            self::TYPE_NISSIN_BACK_ORDER => 'Back Order',
            self::TYPE_NISSIN_DROP_ORDER => 'Drop Order',
        ];
    }

    public static function generateOrderNum()
    {
        $currentYearMonth = date('ym');
        $defaultWoNum = OrderHdr::PREFIX_ORDER_NUM . "-{$currentYearMonth}-00001";

        $cacheNum = Cache::get(static::ORDER_NUM_CACHE_KEY);
        if ($cacheNum) {
            if (strpos($cacheNum, "-${currentYearMonth}-") === false) {
                $orderNum = $defaultWoNum;
            } else {
                $orderNum = ++$cacheNum;
            }

            Cache::put(static::ORDER_NUM_CACHE_KEY, $orderNum);
            return $orderNum;
        }

        $order = DB::table(OrderHdr::getTableName())
            ->where('odr_type', '<>', OrderHdr::TYPE_BACK_ORDER)
            ->where('odr_type', '<>', OrderHdr::TYPE_NISSIN_DROP_ORDER)
            ->orderBy('odr_num', 'desc')
            ->first();

        $lastNum = object_get($order, 'odr_num', '');
        $matches = [];
        $lastNum = preg_match('/^ORD-\d{4}-\d{5}/', $lastNum, $matches);

        if (empty($matches) || strpos($matches[0], "-{$currentYearMonth}-") === false) {
            $orderNum = $defaultWoNum;
        } else {
            $orderNum = $matches[0];
            $orderNum = ++$orderNum;
        }

        Cache::put(static::ORDER_NUM_CACHE_KEY, $orderNum);

        return $orderNum;
    }

    public static function generateJobNo(Warehouse $warehouse, $department)
    {
        $patternJobNo = '%s.%04d.%s';
        $currentYearMonth = date('ym');

        $defaultJobNo = sprintf($patternJobNo, $currentYearMonth, 1, data_get($warehouse, 'job_code'));

        $odrHdr = DB::table(OrderHdr::getTableName())
            ->where('whs_id', $warehouse->whs_id)
            ->where('job_no', 'LIKE', "{$currentYearMonth}%")
            ->orderBy('job_no', 'DESC')
            ->first();

        if (!$odrHdr) {
            return $defaultJobNo;
        }

        $parts = explode('.', $odrHdr->job_no);

        if (count($parts) !== 3) {
            return $defaultJobNo;
        }

        $sequence = $parts[1] + 1;

        return sprintf($patternJobNo, $currentYearMonth, $sequence, data_get($warehouse, 'job_code'));
    }

    public function shipCountry()
    {
        return $this->belongsTo(Country::class, 'ship_to_country', 'id');
    }

    public function shipState()
    {
        return $this->belongsTo(State::class, 'ship_to_state', 'id');
    }

    public function additions()
    {
        return $this->hasMany(OdrHdrTPAddition::class, 'odr_hdr_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cus_id', 'cus_id');
    }

    public function splitOrders()
    {
        return $this->hasMany(OdrSplit::class, 'odr_hdr_id', 'id');
    }

    public function outSorts()
    {
        return $this->hasMany(OdrOutSort::class, 'odr_hdr_id', 'id');
    }

    public function odrCartons()
    {
        return $this->hasMany(OdrCarton::class, 'odr_hdr_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class, 'tp_id');
    }

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class, 'container_type_id');
    }

    public function odrType()
    {
        return $this->belongsTo(OdrType::class, 'odr_type_id');
    }

    public function orderDrops()
    {
        return $this->hasMany(OdrDrop::class, 'odr_hdr_id', 'id');
    }

    public function voucher()
    {
        return $this->belongsTo(OdrVoucher::class,'id','odr_hdr_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
