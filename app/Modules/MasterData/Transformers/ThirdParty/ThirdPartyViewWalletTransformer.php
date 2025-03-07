<?php

namespace App\Modules\MasterData\Transformers\ThirdParty;

use App\Entities\ThirdPartyWallet;
use App\Libraries\Config;
use App\Libraries\Language;
use League\Fractal\TransformerAbstract;

class ThirdPartyViewWalletTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'id' => data_get($model, 'id'),
            'date' => data_get($model, 'date'),
            'type' => data_get($model, 'type'),
            'type_name' => Language::translate(Config::getStatusName(ThirdPartyWallet::TYPE_KEY, data_get($model, 'type'))),

            'description' => data_get($model, 'description'),

            'amount' => data_get($model, 'amount'),
            'current_debt_amount' => data_get($model, 'current_debt_amount'),

            'created_at' => data_get($model, 'created_at'),
            'created_by_name' => data_get($model, 'created_by_name'),
        ];
    }
}
