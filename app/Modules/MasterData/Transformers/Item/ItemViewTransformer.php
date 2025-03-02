<?php

namespace App\Modules\MasterData\Transformers\Item;

use League\Fractal\TransformerAbstract;

class ItemViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'item_id' => data_get($model, 'item_id'),
            'sku' => data_get($model, 'sku'),
            'item_name' => data_get($model, 'item_name'),
            'pack_size' => data_get($model, 'pack_size'),
            'status' => data_get($model, 'status'),
            'status_name' => data_get($model, 'status_name'),
            'uom_id' => data_get($model, 'uom_id'),
            'uom_name' => data_get($model, 'uom_name'),
            'price_suggest' => data_get($model, 'price_suggest'),
            'cus_id' => data_get($model, 'cus_id'),
            'cat_code' => data_get($model, 'cat_code'),
            'cat_name' => data_get($model, 'cat_name'),

            'created_at' => $model->created_at,
            'created_by_name' => data_get($model, 'created_by_name'),
            'updated_at' => $model->updated_at,
            'updated_by_name' => data_get($model, 'updated_by_name')
        ];
    }

    public function getTitleExport()
    {
        return [
            'sku|format_string' => "Model",
            'item_name|format_string' => 'Model Name',
            'item_name_en|format_string' => "Model Name(EN)",
            'uom_name|format_string' => 'UOM',
            'subsidiary_name|format_string' => 'Subsidiary',
            'item_class_name|format_string' => 'Class',
            'vendor_names|format_string' => 'Vendor',
            'cat_name|format_string' => 'Category',
            'item_status_name|format_string' => 'Item Status',
            'upc|format_string' => 'UPC',
            'net_weight|format_number_m3' => 'Net Weight',
            'm3|format_number_m3' => 'Carton @M3',
            'dtl_m3|format_number_m3' => 'Item @M3',
            'volume|format_number_m3' => 'Volume',
            'pack_size|format_number_m3' => 'Pack Size',
            'length|format_number_m3' => 'Length',
            'width|format_number_m3' => 'Width',
            'height|format_number_m3' => 'Height',
            'box_length|format_number_m3' => 'Box Length',
            'box_width|format_number_m3' => 'Box Width',
            'box_height|format_number_m3' => 'Box Height',
            'box_m3|format_number_m3' => 'Box CBM (m3)',
            'carton_length|format_number_m3' => 'Carton Length',
            'carton_width|format_number_m3' => 'Carton Width',
            'carton_height|format_number_m3' => 'Carton Height',
            'carton_weight|format_number_m3' => 'Carton Weight',
            'created_at|format_datetime' => 'Created At',
            'created_by_name|format_string' => 'Created By',
            'updated_at|format_datetime' => 'Updated At',
            'updated_by_name|format_string' => 'Updated By',
        ];
    }
}
