<?php

namespace App\Modules\MasterData\Transformers\Autocomplete;

use League\Fractal\TransformerAbstract;

class AutocompleteThirdPartyTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'tp_id' => data_get($model, 'tp_id'),
            'code' => data_get($model, 'code'),
            'name' => data_get($model, 'name'),
            'des' => data_get($model, 'des'),
            'mobile' => data_get($model, 'mobile'),
            'phone' => data_get($model, 'phone'),
            'email' => data_get($model, 'email'),
            'zip_code' => data_get($model, 'zip_code'),
            'fax' => data_get($model, 'fax'),
            'state_id' => data_get($model, 'state_id'),
            'state_name' => data_get($model, 'state_name', ''),
            'country_id' => data_get($model, 'country_id', ''),
            'country_name' => data_get($model, 'country_name', ''),
            'address' => data_get($model, 'addr_1'),
            'city' => data_get($model, 'city'),
            'vat_code' => data_get($model, 'vat_code'),
        ];
    }
}
