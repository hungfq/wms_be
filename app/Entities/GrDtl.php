<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class GrDtl extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    const STATUS_TYPE = 'GR_DTL_STS';
    const STS_CANCEL = 'CC';
    const STS_RECEIVING = 'RG';
    const STS_COMPLETE = 'RE';

    public $table = 'gr_dtl';

    protected $primaryKey = 'gr_dtl_id';

    protected $fillable = [
        'gr_hdr_id',
        'po_dtl_id',
        'po_num',
        'act_qty',
        'act_ctn_ttl',
        'plt_ttl',
        'dmg_qty',
        'dmg_ctn_ttl',
        'gr_dtl_sts',
        'item_id',
        'lot',
        'bin_loc_id',
        'uom_id',
        'ucc128',
        'created_by',
        'updated_by'
    ];

    public function getStatusTypeValue()
    {
        return self::STATUS_TYPE;
    }

    public function getStatusColumn()
    {
        return 'gr_dtl_sts';
    }

    public function grHdr()
    {
        return $this->belongsTo(GrHdr::class, 'gr_hdr_id');
    }

    public function poDtl()
    {
        return $this->belongsTo(PoDtl::class, 'po_dtl_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function cartons()
    {
        return $this->hasMany(Carton::class, 'gr_dtl_id');
    }
}
