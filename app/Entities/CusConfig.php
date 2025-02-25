<?php

namespace App\Entities;


class CusConfig extends BaseModel
{
    public $table ='cus_config';

    protected $primaryKey = 'id';

    protected $fillable = [
        'cus_id',
        'whs_id',
        'config_name',
        'config_value',
        'ac',
        'sts'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class,'cus_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'whs_id');
    }
}
