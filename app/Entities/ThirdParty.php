<?php

namespace App\Entities;

class ThirdParty extends BaseSoftModel
{
    public $table ='third_party';

    protected $primaryKey = 'tp_id';

    protected $guarded = [
        'tp_id',
    ];

    public function state()
    {
        return $this->belongsTo(State::class,'state_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'cus_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function wallets()
    {
        return $this->hasMany(ThirdPartyWallet::class, 'tp_id');
    }
}
