<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;
use Illuminate\Support\Facades\DB;

class Inventory extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
//    use CustomerRelationshipBelongToTrait;
//    use WarehouseRelationshipBelongToTrait;
//    use ItemRelationshipBelongToTrait;

    public $table = 'inventory';

    protected $primaryKey = 'invt_id';

    protected $guarded = [
        'invt_id',
    ];

    public static function calculateExpQtyModel($model)
    {
        return PoHdr::withoutFilter()
            ->with(['poDtls'])
            ->withCount([
                'poDtls as ttl_exp_qty' => function($q) {
                    $q->select(DB::raw('SUM(exp_qty)'));
                }
            ])
            ->where(function ($q) use ($model) {
                $q->where('whs_id', data_get($model, 'whs_id'))
                    ->where('cus_id', data_get($model, 'cus_id'))
                    ->whereIn('po_sts', [PoHdr::STS_NEW, PoHdr::STS_RECEIVING]);
            })
            ->whereHas('poDtls', function ($q1) use ($model) {
                $q1->where('po_dtl.item_id', data_get($model, 'item_id'))
                    ->where('po_dtl.lot', data_get($model, 'lot'));
            })
            ->get();
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class, 'bin_loc_id');
    }
}
