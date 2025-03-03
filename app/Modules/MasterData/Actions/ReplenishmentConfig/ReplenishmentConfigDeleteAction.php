<?php

namespace App\Modules\MasterData\Actions\ReplenishmentConfig;

use App\Entities\ReplenishmentConfig;
use App\Exceptions\UserException;
use App\Libraries\Language;

class ReplenishmentConfigDeleteAction
{
    public function handle($id)
    {
        $config = ReplenishmentConfig::query()->find($id);
        if (!$config) {
            throw new UserException(Language::translate('Replenishment Config not found'));
        }

        $config->delete();
    }
}
