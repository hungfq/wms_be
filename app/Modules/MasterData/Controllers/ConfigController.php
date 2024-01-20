<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\ConfigApply;
use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Transformers\Config\ConfigApplyTransformer;

class ConfigController extends ApiController
{
    public function getApplyConfigs()
    {
        $configs = ConfigApply::with(['config'])
            ->get();

        return $this->response->collection($configs, new ConfigApplyTransformer);
    }
}
