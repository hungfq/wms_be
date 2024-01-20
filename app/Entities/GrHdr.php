<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;
use Illuminate\Support\Str;

class GrHdr extends BaseSoftModel implements StatusRelationshipInterface
{
//    use CustomerRelationshipBelongToTrait;
//    use WarehouseRelationshipBelongToTrait;
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    const STATUS_TYPE = 'GR_STS';
    const STS_CANCEL = 'CC';
    const STS_RECEIVING = 'RG';
    const STS_COMPLETE = 'RE';

    public $table = 'gr_hdr';

    protected $primaryKey = 'gr_hdr_id';

    protected $guarded = [
        'gr_hdr_id',
    ];

    public function getStatusTypeValue()
    {
        return self::STATUS_TYPE;
    }

    public function getStatusColumn()
    {
        return 'gr_hdr_sts';
    }

    public function container()
    {
        return $this->belongsTo(Container::class, 'ctnr_id');
    }

    public function grDtls()
    {
        return $this->hasMany(GrDtl::class, 'gr_hdr_id');
    }

    public function poHdr()
    {
        return $this->belongsTo(PoHdr::class, 'po_hdr_id');
    }

    public static function generateGrHdrNum($poHdrObj)
    {
        $result = '%s-%02d';

        $poNum = $poHdrObj->po_num;

        $seq = $poHdrObj->grHdrs()->count();

        $seq = (int)$seq + 1;

        return sprintf($result, Str::replaceFirst('ASN', 'GR', $poNum), $seq);
    }
}
