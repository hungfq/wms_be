<?php

namespace App\Modules\Inventory\Transformers;

use App\Libraries\Helpers;
use League\Fractal\TransformerAbstract;

class InventoryByLocationTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'loc_id' => $model->loc_id,
            'loc_code' => $model->loc_code,
            'loc_name' => $model->loc_name,
            'item_id' => $model->item_id,
            'sku' => $model->sku,
            'item_name' => $model->item_name,
            'bin_loc_id' => $model->bin_loc_id,
            'bin_loc_code' => $model->bin_loc_code,
            'bin_loc_name' => $model->bin_loc_name,
            'total_qty' => Helpers::formatNumber($model->total_qty),
            'm3' => Helpers::formatNumberTotalM3($model->m3),
            'total_m3' => Helpers::formatNumberTotalM3(data_get($model, 'total_m3', 0)),
        ];
    }

    public function transformExport($model)
    {
        return [
            'loc_id' => $model->loc_id,
            'loc_code' => $model->loc_code,
            'loc_name' => $model->loc_name,
            'item_id' => $model->item_id,
            'sku' => $model->sku,
            'item_name' => $model->item_name,
            'bin_loc_id' => $model->bin_loc_id,
            'bin_loc_code' => $model->bin_loc_code,
            'bin_loc_name' => $model->bin_loc_name,
            'total_qty' => $model->total_qty,
            'm3' => $model->m3,
            'total_m3' => data_get($model, 'total_m3', 0),
        ];
    }

    public function getTitleExport()
    {
        return [
            'sku|format_string' => 'Model Code',
            'item_name|format_string' => 'Model Name',
            'loc_code|format_string' => 'Location',
            'bin_loc_code|format_string' => 'Bin Location',
            'm3' => '@M3',
            'total_qty|format_number_new' => 'Qty',
            'total_m3' => '∑M3',
        ];
    }
}
