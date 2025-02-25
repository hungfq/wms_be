<?php

namespace App\Modules\MasterData\Validators\ThirdParty;

use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyUpdateDTO;
use App\Validators\AbstractValidator;

class ThirdPartyUpdateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'cus_id' => 'required',
            'code' => 'required',
            'name' => 'required',
            'address' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city' => 'required',
            'phone' => 'nullable',
            'mobile' => 'nullable',
            'fax' => 'nullable|max:15',
        ];
    }

    public function toDTO()
    {
        return ThirdPartyUpdateDTO::fromRequest();
    }
}
