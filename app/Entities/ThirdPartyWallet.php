<?php

namespace App\Entities;

class ThirdPartyWallet extends BaseSoftModel
{
    public $table = 'third_party_wallets';

    protected $primaryKey = 'id';

    protected $guarded = [
        'id',
    ];

    const TYPE_KEY = "THIRD-PARTY-WALLET-TYPE";
    const TYPE_ORDER = "OD";
    const TYPE_INCREASE_DEBT = "ID";
    const TYPE_DECREASE_DEBT = "DD";

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class, 'tp_id');
    }

    public function order()
    {
        return $this->belongsTo(OrderHdr::class, 'ref_odr_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
