<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class WhsConfig extends Model
{
    const CONFIG_KEY = 'WHS_CONFIG';

    //Common Group
    const CONFIG_PRINTER = 'PRINTERS';
    const CONFIG_PRINTER_FORMATS = 'PRINTER_FORMATS';
    const CONFIG_QRCODE = 'QRCODE';
    const CONFIG_CARTON_DAMAGE_REPORT_NO = 'CTN_DAMAGE_REPORT_NO';
    const CONFIG_EXPORT_LIMIT = 'EXPORT_LIMIT';
    const CONFIG_TIME_OUT_NO_ACTION = 'TIME_OUT_NO_ACTION';
    const CONFIG_REQUIRE_SELECTED_CUS = 'REQUIRE_SELECTED_CUS';
    const CONFIG_SHARP_CAPACITY = 'SHARP_CAPACITY';
    const CONFIG_HISENSE_CAPACITY = 'HISENSE_CAPACITY';
    const CONFIG_DASHBOARD_TV_AUTO_RELOAD = 'DASHBOARD_TV_AUTO_RELOAD';
    //Inbound Group
    const CONFIG_PUT_SUG_LOC = 'PUT_SUG_LOC';
    const CONFIG_ASSIGN_PUTTER = 'ASSIGN_PUTTER';
    const CONFIG_REQUIRE_PUTAWAY_LOCATION = 'REQUIRE_PUTAWAY_LOCATION';
    const CONFIG_CONFIRM_SCAN_MODEL = 'CONFIRM_SCAN_MODEL';
    //Outbound Group
    const CONFIG_OUTBOUND_PICKING_ITEM_NON_SERIAL_ALLOW_INPUT_QTY = 'PICKING_NON_SERI_ALLOW_INPUT_QTY';
    const CONFIG_OUTBOUND_PICKING_REQUIRE_PALLET = 'PICKING_REQUIRE_PALLET';
    const CONFIG_OUTBOUND_PICKING_REQUIRE_MIN_TIME = 'PICKING_REQUIRE_MIN_TIME';
    const CONFIG_OUTBOUND_PICKING_CONFIRM_SCAN_MODEL = 'PICKING_CONFIRM_SCAN_MODEL';
    const CONFIG_REQUIRE_SET_PICK_LOCATION = 'REQUIRE_SET_PICK_LOCATION';
    const CONFIG_CAN_COMBINE_WAVEPICK = 'CAN_COMBINE_WAVEPICK';
    const CONFIG_USE_CONTAINER = 'USE_CONTAINER';
    const CONFIG_PICK_BY_WEB = 'PICK_BY_WEB';

    const CONFIG_ACTIVE = 'Y';
    const CONFIG_INACTIVE = 'N';

    public $table = 'whs_config';

    protected $primaryKey = 'id';

    protected $fillable = [
        'whs_id',
        'config_code',
        'config_value',
        'ac',
        'json_value',
    ];

    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $casts = [
        'json_value' => 'json'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'whs_id');
    }
}
