<?php

namespace App\Modules\MasterData\Actions\ThirdParty;

use App\Entities\ThirdParty;
use App\Exceptions\UserException;
use App\Libraries\Language;
use Illuminate\Support\Facades\DB;

class ThirdPartyShowAction
{
    public function handle($tpId)
    {
        $thirdParty = ThirdParty::query()->find($tpId);

        if (!$thirdParty) {
            throw new UserException(Language::translate('Third Party does not exist'));
        }

        if ($stateId = data_get($thirdParty, 'state_id')) {
            $country = DB::table('countries')
                ->select('countries.id', 'countries.name', 'states.name AS state_name')
                ->join('states', 'states.country_id', '=', 'countries.id')
                ->where('states.id', $stateId)->first();

            if ($country) {
                $thirdParty->country_name = $country->name;
                $thirdParty->country_id = $country->id;
                $thirdParty->state_name = $country->state_name;
            }
        }

        return $thirdParty;
    }
}
