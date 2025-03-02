<?php

namespace App\Modules\MasterData\Transformers\Autocomplete;

use App\Libraries\Helpers;
use App\Libraries\Language;
use League\Fractal\TransformerAbstract;

class AutocompleteItemTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'item_id' => data_get($model,'item_id'),
            'item_name' => data_get($model,'item_name'),
            'plt_type_id' => data_get($model,'plt_type_id'),
            'plt_type_name' => data_get($model, 'palletType.name'),
            'sku' => data_get($model,'sku'),
            'size' => data_get($model,'size'),
            'color' => data_get($model,'color'),
            'des' => data_get($model,'des'),
            'serial' => data_get($model,'serial'),
            'serial_text' => Helpers::getTxtYesOrNoOfSerial(data_get($model,'serial'), true),
            'pack_size' => data_get($model,'pack_size'),
            'length' => data_get($model,'length'),
            'width' => data_get($model,'width'),
            'height' => data_get($model,'height'),
            'm3' => data_get($model,'m3'),
            'weight' => data_get($model,'weight'),
            'net_weight' => data_get($model,'net_weight'),
            'volume' => data_get($model,'volume'),
            'status' => data_get($model,'status'),
            'status_name' => data_get($model, 'statuses.sts_name'),
            'trans_status_name' => Language::translate(data_get($model, 'statuses.sts_name')),
            'cube' => data_get($model,'cube'),
            'cat_code' => data_get($model,'cat_code'),
            'cat_name' => data_get($model, 'categoryCode.name'),
            'spc_hdl_code' => data_get($model,'spc_hdl_code'),
            'spc_hdl_name' => data_get($model,'spcHdl.spc_hdl_name'),
            'cus_id' => data_get($model,'cus_id'),
            'cus_name' => data_get($model,'applyFilterCustomer.name'),
            'cus_code' => data_get($model,'applyFilterCustomer.code'),
            'uom_id' => data_get($model,'uom_id'),
            'uom_name' => data_get($model,'uom.name'),
            'uom_code' => data_get($model,'uom.code'),
            'prefix' => data_get($model,'prefix'),
            'suffix' => data_get($model,'suffix'),
            'vendor_id' => data_get($model, 'vendor_id'),
            'vendor_code' => data_get($model, 'vendor_code'),
            'vendor_name' => data_get($model, 'vendor_name'),
        ];
    }
}
