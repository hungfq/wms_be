<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Warehouse extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    const STATUS_TYPE = 'WHS_STS';
    public $table = 'warehouses';

    protected $primaryKey = 'whs_id';

    protected $guarded = [
        'whs_id',
    ];

    public function getStatusColumn()
    {
        return 'status';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_TYPE_WAREHOUSE;
    }

    public function address()
    {
        return $this->hasOne(WhsAddress::class, 'whs_id', 'whs_id');
    }

    public function contact()
    {
        return $this->hasOne(WhsContact::class, 'whs_id', 'whs_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'whs_id');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_warehouse', 'whs_id', 'cus_id');
    }

    public function configs()
    {
        return $this->hasMany(WhsConfig::class, 'whs_id');
    }
}
