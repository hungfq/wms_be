<?php

namespace App\Modules\MasterData\Actions\ThirdParty;

use App\Entities\ThirdParty;
use App\Entities\ThirdPartyWallet;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyUpdateWalletDTO;
use Carbon\Carbon;

class ThirdPartyUpdateWalletAction
{
    public ThirdPartyUpdateWalletDTO $dto;
    public $thirdParty;

    /**
     * @param ThirdPartyUpdateWalletDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->findThirdParty()
            ->updateThirdParty();
    }

    public function findThirdParty()
    {
        $this->thirdParty = ThirdParty::query()->find(data_get($this->dto, 'tp_id'));

        if (!$this->thirdParty) {
            throw new UserException(Language::translate('Third Party does not exists'));
        }

        return $this;
    }

    public function updateThirdParty()
    {
        if ($this->dto->type == ThirdPartyWallet::TYPE_INCREASE_DEBT) {
            $this->thirdParty->debt_amount += $this->dto->amount;
        } else {
            $this->thirdParty->debt_amount -= $this->dto->amount;
        }
        $this->thirdParty->save();

        $this->thirdParty->wallets()->create([
            'date' => Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
            'type' => $this->dto->type,
            'description' => $this->dto->description,
            'amount' => $this->dto->amount,
            'current_debt_amount' => $this->thirdParty->debt_amount,
        ]);

        return $this;
    }
}
