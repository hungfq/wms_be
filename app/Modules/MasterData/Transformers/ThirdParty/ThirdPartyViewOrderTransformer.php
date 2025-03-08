<?php

namespace App\Modules\MasterData\Transformers\ThirdParty;

use App\Libraries\Language;
use League\Fractal\TransformerAbstract;

class ThirdPartyViewOrderTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'odr_id' => data_get($model, 'id'),
            'odr_num' => data_get($model, 'odr_num'),
            'odr_sts_name' => Language::translate(data_get($model, 'statuses.sts_name')),

            'amount' => data_get($model, 'amount'),

            'created_at' => data_get($model, 'created_at'),
            'created_by_name' => data_get($model, 'created_by_name'),
        ];
    }
}
