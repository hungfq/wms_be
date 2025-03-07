<?php

namespace App\Modules\MasterData\Transformers\ThirdParty;

use League\Fractal\TransformerAbstract;

class ThirdPartyShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'tp_id' => data_get($model, 'tp_id'),
            'cus_id' => data_get($model, 'cus_id'),
            'cus_name' => data_get($model, 'cus_name'),
            'code' => data_get($model, 'code'),
            'name' => data_get($model, 'name'),
            'debt_amount' => data_get($model, 'debt_amount'),
            'des' => data_get($model, 'des'),
            'mobile' => data_get($model, 'mobile'),
            'phone' => data_get($model, 'phone'),
            'email' => data_get($model, 'email'),
            'zip_code' => data_get($model, 'zip_code'),
            'state_id' => data_get($model, 'state_id'),
            'state_name' => data_get($model, 'state_name'),
            'area_id' => data_get($model, 'state.area_id'),
            'area_name' => data_get($model, 'state.area.name'),
            'country_id' => data_get($model, 'country_id'),
            'country_name' => data_get($model, 'country_name'),
            'address' => data_get($model, 'addr_1'),
            'location' => data_get($model, 'location'),
            'city' => data_get($model, 'city'),
            'vat_code' => data_get($model, 'vat_code'),
            'fax' => data_get($model, 'fax'),
        ];
    }
}
