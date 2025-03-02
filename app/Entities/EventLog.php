<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;

class EventLog extends BaseSoftModel
{
    use CreatedByRelationshipTrait;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'info_params' => 'json'
    ];

    //PO event code
    const PO_CREATE = 'POCR';
    const PO_UPDATE = 'POUP';
    const GR_CREATE = 'GRCR';
    const GR_COMPLETE = 'GRRE';

    // order
    const ORDER_CREATE = 'ODCR';
    const ORDER_UPDATE = 'ODUD';
    const ORDER_ALLOCATE = 'ODAL';
    const ORDER_PICKING = 'ODPK';
    const ORDER_PICKED = 'ODPD';
    const ORDER_OUT_SORTED = 'ODOD';
    const ORDER_SCHEDULE_TO_SHIP = 'ODSS';
    const ORDER_SHIP = 'ODSH';
    const ORDER_REVERT = 'ODRV';
    const ORDER_CANCEL = 'ODCC';
    // wavepick
    const WAVE_PICK_CREATED = 'WVCR';
    const WAVE_PICK_PICKING = 'WVPK';
    const WAVE_PICK_PICKED = 'WVPD';
    const WAVE_PICK_CANCEL = 'WVCC';
}
