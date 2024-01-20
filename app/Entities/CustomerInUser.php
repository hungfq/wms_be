<?php

namespace App\Entities;

class CustomerInUser extends BaseModel
{
    public $table ='cus_in_user';

    protected $primaryKey = false;

    public $incrementing = false;

    protected $fillable = [
        'cus_id',
        'whs_id',
        'user_id'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'whs_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cus_id');
    }
}
