<?php

namespace App\Entities;

class Customer extends BaseSoftModel
{
    const STATUS_KEY = 'CUSTOMER_STATUS';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    const STATUS_TYPE = 'CUS_STS';
    public $table ='customers';

    protected $primaryKey = 'cus_id';

    protected $guarded = ['cus_id'];

    public function algorithm()
    {
        return $this->hasOne(CusConfig::class, 'cus_id', 'cus_id')
                ->where('config_name', 'picking_algorithm')
                ->where('ac', 'Y');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

//    public function address()
//    {
//        return $this->hasOne(CusAddress::class, 'cus_id');
//    }
//
//    public function contact()
//    {
//        return $this->hasOne(CusContact::class, 'cus_id');
//    }

    public function configs()
    {
        return $this->hasMany(CusConfig::class, 'cus_id', 'cus_id');
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'customer_warehouse', 'cus_id', 'whs_id');
    }

//    public function currency()
//    {
//        return $this->belongsTo(Config::class, 'currency_id', 'id');
//    }
}
