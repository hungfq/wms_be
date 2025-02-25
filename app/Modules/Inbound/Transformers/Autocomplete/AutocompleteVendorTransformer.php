<?php

namespace App\Modules\Inbound\Transformers\Autocomplete;

use League\Fractal\TransformerAbstract;

class AutocompleteVendorTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => data_get($model, 'id'),
            'code' => data_get($model, 'code'),
            'name' => data_get($model, 'name'),
            'vat_code' => data_get($model, 'vat_code'),
            'full_address' => data_get($model, 'full_address'),
        ];
    }
}
