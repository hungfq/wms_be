<?php

namespace App\Modules\MasterData\Transformers\Config;

use League\Fractal\TransformerAbstract;

class ConfigApplyTransformer extends TransformerAbstract {

    public function transform($configApply)
    {
        return [
            'id' => data_get($configApply, 'config.id'),
            'name' => data_get($configApply, 'config.name'),
            'code' => data_get($configApply, 'config.code'),
            'label' => data_get($configApply, 'config.label'),
            'value' => data_get($configApply, 'config.value_fe')
        ];
    }
}