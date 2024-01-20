<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;
use Illuminate\Support\Facades\DB;

class OdrDrop extends BaseSoftModel
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

    const TYPE_DROP = 'DROP';
    const TYPE_DAMAGE = 'DAMAGE';

    const STATUS_TYPE = 'ORDER_DROP_STS';

    const STS_NEW = 'NW';
    const STS_COMPLETE = 'CP';
    const STS_CANCEL = 'CC';

    const PREFIX_ORDER_NUM = 'RE';

    protected $guarded = [
        'id',
    ];

    public static function generateOrderNum()
    {
        $currentYearMonth = date('ym');
        $defaultWoNum = OdrDrop::PREFIX_ORDER_NUM . "-${currentYearMonth}-00001";
        $order = DB::table(OdrDrop::getTableName())
            ->orderBy('odr_num', 'desc')
            ->first();

        $lastNum = object_get($order, 'odr_num', '');
        $lastNum = preg_replace('/-\d{1,2}$/', '',  $lastNum);

        if (empty($lastNum) || strpos($lastNum, "-${currentYearMonth}-") === false) {
            return $defaultWoNum;
        }

        return ++$lastNum;
    }

    public function orderOrigin()
    {
        return $this->belongsTo(OrderHdr::class, 'odr_hdr_id');
    }

    public function orderDtlOrigin()
    {
        return $this->belongsTo(OrderDtl::class, 'odr_dtl_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
