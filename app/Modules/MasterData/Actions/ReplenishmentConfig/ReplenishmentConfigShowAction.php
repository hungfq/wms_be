<?php

namespace App\Modules\MasterData\Actions\ReplenishmentConfig;

use App\Entities\ReplenishmentConfig;

class ReplenishmentConfigShowAction
{
    public function handle($id)
    {
        return ReplenishmentConfig::query()->find($id);
    }
}
