<?php

namespace App\Modules\MasterData\Actions\ThirdParty;

use App\Entities\ThirdParty;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyStoreDTO;

class ThirdPartyStoreAction
{
    public ThirdPartyStoreDTO $dto;
    public $thirdParty;

    /**
     * @param ThirdPartyStoreDTO $dto
     */

    public function handle($dto)
    {
        $this->dto = $dto;

        $this->validateDataInput()
            ->makeThirdParty();
    }

    public function validateDataInput()
    {
        $isDuplicateCode = ThirdParty::query()
            ->where('code', data_get($this->dto, 'code'))
            ->first();

        if ($isDuplicateCode) {
            throw new UserException(Language::translate('The Third Party code has already been taken'));
        }

        return $this;
    }

    public function makeThirdParty()
    {
        $param = [
            'cus_id' => data_get($this->dto, 'cus_id'),
            'tp_group_id' => data_get($this->dto, 'tp_group_id'),
            'code' => data_get($this->dto, 'code'),
            'name' => data_get($this->dto, 'name'),
            'phone' => data_get($this->dto, 'phone'),
            'mobile' => data_get($this->dto, 'mobile'),
            'email' => data_get($this->dto, 'email'),
            'addr_1' => data_get($this->dto, 'address'),
            'location' => data_get($this->dto, 'location'),
            'state_id' => data_get($this->dto, 'state_id'),
            'city' => data_get($this->dto, 'city'),
            'zip_code' => data_get($this->dto, 'zip_code'),
            'vat_code' => data_get($this->dto, 'vat_code'),
            'fax' => data_get($this->dto, 'fax'),
            'des' => data_get($this->dto, 'des'),
        ];

        $this->thirdParty = ThirdParty::query()->create($param);

        return $this;
    }
}
