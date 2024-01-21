<?php

namespace App\Modules\Inbound\DTO\PO;

use App\Entities\Item;
use App\Entities\PoDtl;
use Spatie\DataTransferObject\FlexibleDataTransferObject;

class PoUpdateDetailDTO extends FlexibleDataTransferObject
{
    public $po_dtl_id;
    public $bin_loc_id;
    public $vendor_id;
    public $item_id;
    public $exp_qty;
    public $exp_ctn_ttl;
    public $sku;
    public $size;
    public $color;
    public $lot;
    public $pack_size;
    public $serial;
    public $is_delete;
    public $item_id_lot_is_delete;
    public $remark;
    public $exp_dt;
    public $received_dt;
    public $po_dtl_sts;

    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);

        $this->size = data_get($parameters, 'size') ?? Item::DEFAULT_SIZE;
        $this->color = data_get($parameters, 'color') ?? Item::DEFAULT_COLOR;
        $this->lot = data_get($parameters, 'lot') ?? PoDtl::getDefaultLot();
        $this->is_delete = data_get($parameters, 'is_delete') ?? 0;
        $this->bin_loc_id = data_get($parameters, 'bin_loc_id');
        $this->item_id_lot_is_delete = sprintf('%s_%s_%s', $this->item_id, $this->lot, $this->is_delete);
        $this->po_dtl_sts = data_get($parameters, 'po_dtl_sts');
    }
}
