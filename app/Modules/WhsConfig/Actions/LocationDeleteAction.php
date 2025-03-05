<?php

namespace App\Modules\WhsConfig\Actions;

use App\Entities\Carton;
use App\Entities\Location;
use App\Exceptions\UserException;
use App\Libraries\Language;

class LocationDeleteAction
{
    public function handle($whsId, $ids)
    {
        $haveCarton = Carton::query()
            ->where('whs_id', '=', $whsId)
            ->whereIn('loc_id', $ids)
            ->whereIn('ctn_sts', [Carton::STS_ACTIVE, Carton::STS_RECEIVING])
            ->first();
        if ($haveCarton) {
            throw new UserException(Language::translate('Locations containing goods cannot be deleted'));
        }

        Location::query()
            ->where('whs_id', '=', $whsId)
            ->whereIn('loc_id', $ids)
            ->delete();
    }
}
