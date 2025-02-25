<?php

namespace App\Modules\MasterData\Transformers\Autocomplete;

use League\Fractal\TransformerAbstract;

class AutocompleteLocationTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'loc_id' => data_get($model, 'loc_id'),
            'loc_code' => data_get($model, 'loc_code'),
            'loc_name' => data_get($model, 'loc_name'),
        ];
    }
}
