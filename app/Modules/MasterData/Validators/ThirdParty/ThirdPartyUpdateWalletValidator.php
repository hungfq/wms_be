<?php

namespace App\Modules\MasterData\Validators\ThirdParty;

use App\Entities\ThirdPartyWallet;
use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyUpdateWalletDTO;
use App\Validators\AbstractValidator;

class ThirdPartyUpdateWalletValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'tp_id' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required|in:' . implode(',', [ThirdPartyWallet::TYPE_DECREASE_DEBT, ThirdPartyWallet::TYPE_INCREASE_DEBT]),
            'description' => 'required',
        ];
    }

    public function toDTO()
    {
        return ThirdPartyUpdateWalletDTO::fromRequest();
    }
}
