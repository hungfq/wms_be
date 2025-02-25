<?php

namespace App\Modules\Inbound\Transformers\Autocomplete;

use League\Fractal\TransformerAbstract;

class AutocompletePoNumTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'po_hdr_id' => data_get($model, 'po_hdr_id'),
            'po_num' => data_get($model, 'po_num'),
        ];
    }
}
