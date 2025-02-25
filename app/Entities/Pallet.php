<?php

namespace App\Entities;



use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Pallet extends BaseSoftModel implements StatusRelationshipInterface
{
//    use CustomerRelationshipBelongToTrait;
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
//    use WarehouseRelationshipBelongToTrait;
    use StatusesRelationshipTrait;

    const STATUS_KEY = 'PALLET_STATUS';

    const STS_ACTIVE = 'AC';
    const STS_RECEIVING = 'RG';
    const STS_PICKED = 'PD';
    const STS_SHIPPED = 'SH';

    const IS_FULL_YES = 1;
    const IS_FULL_NO = 0;

    const PREFIX_NUM = 'PL';
    const PREFIX_VIR = 'VIR';

    public $table = 'pallet';

    protected $primaryKey = 'plt_id';

    protected $guarded = [
        'plt_id',
    ];

    public $columnDefaultCalcM3 = 'cartons.ctn_ttl';

    public function getStatusColumn()
    {
        return 'plt_sts';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_PALLET_TYPE;
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id');
    }

    public function cartons()
    {
        return $this->hasMany(Carton::class, 'plt_id');
    }

    public static function generatePalletNum($whsId = null, $poHdr = null)
    {
        return self::generatePltRfid($whsId, self::PREFIX_VIR);
    }

    public function scopeVirtualPallet($query)
    {
        return $query->where('pallet.rfid', 'LIKE', self::PREFIX_VIR . '-%');
    }

    public static function generatePltRfid($whsId = null, $prefix = null)
    {
        $prefix = $prefix ?? 'PLT';

        $whsId = $whsId ?: Data::getCurWhs();

        $warehouse = Warehouse::findOrFail($whsId);

        if ($prefix === 'PLT') {
            $fullPrefix = strtoupper('PLT' . '-' . date('ym'));
        } else if ($prefix === 'BOX') {
            $fullPrefix = strtoupper('BOX' . '-' . date('ym'));
        } else if ($prefix === 'VIR') {
            $fullPrefix = strtoupper('VIR' . '-' . date('ym'));
        } else if ($prefix === 'FOC') {
            $fullPrefix = strtoupper('FOC' . '-' . date('ym'));
        }


        $pallet = Pallet::where('whs_id', $warehouse->whs_id)
            ->where(function ($q) use ($fullPrefix) {
                $q->where('rfid', 'LIKE', $fullPrefix . '-%');
            })
            ->orderBy('rfid', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$pallet) {
            return sprintf('%s-%05d', $fullPrefix, 1);
        }

        $parts = explode('-', $pallet->rfid);

        $prefix = $parts[0] . '-' . $parts[1];
        $lastNum = (int)$parts[count($parts) - 1];

        return sprintf('%s-%05d', $prefix, $lastNum + 1);
    }

    public function items()
    {
        return $this->belongsToMany(
            Item::class,
            'cartons',
            'plt_id',
            'item_id'
        )->groupBy('items.item_id');
    }

    public static function generateElmichPltNum($whsId, $pltRfid)
    {
        $whsId = $whsId ?: Data::getCurWhs();

        $warehouse = Warehouse::findOrFail($whsId);

        $pallet = Pallet::where('whs_id', $warehouse->whs_id)
            ->where('plt_num', 'LIKE', $pltRfid . '-%')
            ->orderBy('plt_num', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$pallet) {
            return sprintf('%s-%05d', $pltRfid, 1);
        }

        $parts = explode('-', $pallet->plt_num);

        $lastNum = (int)$parts[count($parts) - 1];

        return sprintf('%s-%05d', $pltRfid, $lastNum + 1);
    }
}
