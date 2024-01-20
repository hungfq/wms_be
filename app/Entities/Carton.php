<?php

namespace App\Entities;

use App\Libraries\Config;
use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;
use Illuminate\Support\Facades\DB;

class Carton extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
//    use CustomerRelationshipBelongToTrait;
//    use WarehouseRelationshipBelongToTrait;
    use StatusesRelationshipTrait;

    /** @var int Carton was not damaged */
    const IS_DAMAGE_FALSE = 0;

    /** @var int Carton was damaged */
    const IS_DAMAGE_TRUE = 1;

    const STS_ACTIVE = 'AC';
    const STS_INACTIVE = 'IA';
    const STS_RECEIVING = 'RG';
    const STS_PICKED = 'PD';
    const STS_SHIPPED = 'SH';
    const STS_LOCKED = 'LK';
    const STS_OUT_SORTED = 'OD';
    const STS_PUT_BACK = 'PB';
    // replenishment statuses
    const STS_REPLENISHMENT_PICKED = 'RP';


    public $table = 'cartons';

    protected $primaryKey = 'ctn_id';

    protected $guarded = [
        'ctn_id',
    ];

    protected $dates = [
        'picked_date', 'storaged_date', 'expired_date', 'manufacture_date'
    ];


    /**
     * @return string
     */
    public function getStatusColumn()
    {
        return 'ctn_sts';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_CARTON_TYPE;
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id');
    }

    public function pallet()
    {
        return $this->belongsTo(Pallet::class, 'plt_id');
    }

    public function grHdr()
    {
        return $this->belongsTo(GrHdr::class, 'gr_hdr_id');
    }

    public function grDtl()
    {
        return $this->belongsTo(GrDtl::class, 'gr_dtl_id');
    }

    public function poDtl()
    {
        return $this->belongsTo(PoDtl::class, 'po_dtl_id');
    }

    public function getInventory()
    {
        return $this->belongsTo(Inventory::class, 'item_id', 'item_id')
            ->where('lot', $this->lot)
            ->where('cus_id', $this->cus_id)
            ->where('bin_loc_id', $this->bin_loc_id)
            ->where('whs_id', $this->whs_id)
            ->first();
    }

    public function scopeFilterByWvDtl($query, $wvDtl)
    {
        $query->whereHas('location', function ($q) {
            $q->whereIn('loc_sts', ['AC']);
        });

        if ($wvDtl->lot != Config::ANY) {
            $query->where($this->qualifyColumn('lot'), $wvDtl->lot);
        }

        $query->where($this->qualifyColumn('item_id'), $wvDtl->item_id);
        $query->where($this->qualifyColumn('cus_id'), $wvDtl->cus_id);
        $query->where($this->qualifyColumn('whs_id'), $wvDtl->whs_id);
        $query->where($this->qualifyColumn('bin_loc_id'), $wvDtl->bin_loc_id);
        $query->where($this->qualifyColumn('ctn_sts'), Config::getStatusCode('CARTON_STATUS', 'Active'));
        $query->whereNotNull($this->qualifyColumn('loc_id'));
        $query->whereNotNull($this->qualifyColumn('plt_id'));
    }

    public function images()
    {
        return $this->hasMany(CartonImage::class, 'ctn_id');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class, 'bin_loc_id');
    }

    public static function generateNum($rfid)
    {
        if (!$rfid) {
            return null;
        }

        $total = Carton::where('rfid', $rfid)->count();

        return sprintf('%s-%02d', $rfid, $total + 1);
    }

    public function odrCartons()
    {
        return $this->hasMany(OdrCarton::class, 'ctn_id');
    }
}
