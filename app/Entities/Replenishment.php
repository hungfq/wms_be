<?php

namespace App\Entities;

use App\Libraries\Data;
use Illuminate\Support\Facades\DB;

class Replenishment extends BaseSoftModel
{
    public $table = 'replenishment_hdrs';

    protected $guarded = [
        'id',
    ];

    const STATUS_KEY = 'REPLENISHMENT_STATUSES';

    const STATUS_NEW = 'NW';
    const STATUS_REPLENISHMENT_PICKED = 'RP';
    const STATUS_REPLENISHING = 'RG';
    const STATUS_REPLENISHED = 'RE';
    const STATUS_CANCELED = 'CC';

    const CREATE_FROM_REPLENISHMENT_WAVEPICK = 'WV';
    const CREATE_FROM_REPLENISHMENT_SYSTEM = 'ST';

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'whs_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'item_id', 'item_id');
    }

    public function details()
    {
        return $this->hasMany(ReplenishmentDtl::class, 'repln_id');
    }

    public function summaries()
    {
        return $this->hasMany(ReplenishmentSummary::class, 'repln_id');
    }

    public static function generateReplenishmentNum()
    {
        $currentYearMonth = date('ym');
        $defaultNum = "RL-${currentYearMonth}-00001";
        $replenishment = DB::table(Replenishment::getTableName())
            ->where('whs_id', Data::getCurWhs())
            ->orderBy('id', 'desc')
            ->first();

        if (!$replenishment) {
            return $defaultNum;
        }

        [$prefix, $yearMonth, $lastNum] = explode('-', $replenishment->replenishment_num);
        if ($currentYearMonth != $yearMonth) {
            return $defaultNum;
        }

        return sprintf('%s-%s-%05d', $prefix, $currentYearMonth, ++$lastNum);
    }

    public function replenishPicks()
    {
        return $this->hasMany(ReplenishmentPick::class, 'repln_id');
    }

    public function replenishPuts()
    {
        return $this->hasMany(ReplenishmentPut::class, 'repln_id');
    }

    public function wvHdr()
    {
        return $this->belongsTo(WvHdr::class, 'wv_hdr_id');
    }

    public static function createdFrom()
    {
        return [
            self::CREATE_FROM_REPLENISHMENT_WAVEPICK => 'WAVE PICK',
            self::CREATE_FROM_REPLENISHMENT_SYSTEM => 'SYSTEM'
        ];
    }
}
