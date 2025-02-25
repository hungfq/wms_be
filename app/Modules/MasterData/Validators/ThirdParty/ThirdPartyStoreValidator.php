<?php

namespace App\Modules\MasterData\Validators\ThirdParty;

use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyStoreDTO;
use App\Validators\AbstractValidator;

class ThirdPartyStoreValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'cus_id' => 'required',
            'tp_group_id' => 'nullable|integer',
            'code' => 'required',
            'name' => 'required',
            'address' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city' => 'required',
            'phone' => 'nullable',
            'mobile' => 'nullable',
            'fax' => 'nullable|max:15',
            'group_channel_ids' => 'nullable|array',
        ];
    }


    public function toDTO()
    {
        return ThirdPartyStoreDTO::fromRequest();
    }
}
