<?php

namespace App\Entities;


class Statuses extends BaseModel
{
    const STATUS_TYPE_PO = 'PO';
    const STATUS_TYPE_EVALUATE = 'EVALUATE';
    const STATUS_TYPE_PO_DTL = 'PO_DTL';

    const STATUS_TYPE_PUT_BACK = 'PB';
    const STATUS_TYPE_PUT_BACK_DTL = 'PB_DTL';

    const STATUS_TYPE_BLOCK_STOCK = 'BLOCK_STOCK';
    const STATUS_TYPE_BLOCK_STOCK_DTL = 'BLOCK_STOCK_DTL';

    const STATUS_TYPE_COUNTRY = 'COUNTRY';
    const STATUS_TYPE_LOC_TYPE = 'LOC_TYPE';
    const STATUS_TYPE_ZONE_TYPE = 'ZONE_TYPE';
    const STATUS_TYPE_ZONE = 'ZONE';
    const STATUS_TYPE_WAREHOUSE = 'WHS_STS';
    const STATUS_PALLET_TYPE = 'PALLT_STS';
    const STATUS_CARTON_TYPE = 'CARTON_STS';
    const STATUS_ITEM_TYPE = 'ITEM';
    const STATUS_TYPE_RELOCATE = 'RELOCATE';
    const STATUS_TYPE_RELOCATE_DTL = 'RELOCATE_DTL';
    const STATUS_TYPE_CHARGE_TYPE = 'CHARGE_TYPE';

    const STATUS_VEHICLE_TYPE = 'VEHICLE';

    public $table = 'statuses';

    protected $primaryKey = false;

    protected $fillable = [
        'sts_type',
        'sts_code',
        'sts_name',
        'seq',
        'des'
    ];
}
