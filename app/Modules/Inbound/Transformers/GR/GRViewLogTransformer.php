<?php

namespace App\Modules\Inbound\Transformers\GR;

use League\Fractal\TransformerAbstract;

class GRViewLogTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'ctn_id' => data_get($model, 'ctn_id'),
            'plt_id' => data_get($model, 'plt_id'),
            'plt_rfid' => data_get($model, 'plt_rfid'),
            'plt_is_full' => data_get($model, 'plt_is_full'),
            'loc_id' => data_get($model, 'loc_id'),
            'loc_code' => data_get($model, 'loc_code'),
            'loc_name' => data_get($model, 'loc_name'),
            'item_id' => data_get($model, 'item_id'),
            'bin_loc_id' => data_get($model, 'bin_loc_id'),
            'bin_loc_code' => data_get($model, 'bin_loc_code'),
            'bin_loc_name' => data_get($model, 'bin_loc_name'),
            'sku' => data_get($model, 'sku'),
            'item_name' => data_get($model, 'item_name'),
            'lot' => data_get($model, 'lot'),
            'pack_size' => data_get($model, 'pack_size'),
            'manufacture_date' => data_get($model, 'manufacture_date'),
            'des' => data_get($model, 'des'),
            'ttl_ctn_qty' => data_get($model, 'ttl_ctn_qty'),
            'ttl_piece_qty' => data_get($model, 'ttl_piece_qty'),
        ];
    }
}
