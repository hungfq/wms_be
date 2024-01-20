<?php

namespace App\Entities;


use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Location extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    /** @var string Location status is active */
    const LOCATION_STATUS_ACTIVE = 'AC';
    /** @var string Location status was locked */
    const LOCATION_STATUS_LOCKED = 'LK';

    const STATUS_TYPE = 'LOCATION_STS';
    const IS_FULL_YES = 1;
    const IS_FULL_NO = 0;
    const GOODS_TYPE = 'LOCATION_GOODS_TYPE';
    const GOODS_TYPE_RETAIL = 'RT';
    const GOODS_TYPE_WHOLESALE = 'WS';
    const GOODS_TYPE_WAVEPICK = 'WP';

    public $table = 'locations';

    protected $primaryKey = 'loc_id';

    protected $guarded = [
        'loc_id',
    ];

    public $columnDefaultCalcM3 = 'cartons.ctn_ttl';

    public function getStatusTypeValue()
    {
        return self::STATUS_TYPE;
    }

    public function getStatusColumn()
    {
        return 'loc_sts';
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class, 'loc_type_id');
    }

    public function pallets()
    {
        return $this->hasMany(Pallet::class, 'loc_id');
    }

    public function poPallets()
    {
        return $this->hasMany(PoPallet::class, 'location_id');
    }

    public function cartons()
    {
        return $this->hasMany(Carton::class, 'loc_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'whs_id')
            ->withoutGlobalScope(FilterWarehouseScope::class)
            ->withoutGlobalScope(FilterCustomerScope::class);
    }

    public function palletType()
    {
        return $this->belongsTo(PalletType::class, 'plt_type_id', 'id');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function groups()
    {
        return $this->morphToMany(Groups::class, 'groupables');
    }
}
