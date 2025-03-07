<?php

namespace App\Modules\MasterData\Actions\ThirdParty;

use App\Entities\ThirdPartyWallet;
use App\Libraries\Helpers;
use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyViewWalletDTO;

class ThirdPartyViewWalletAction
{
    public ThirdPartyViewWalletDTO $dto;

    /**
     * @param ThirdPartyViewWalletDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = ThirdPartyWallet::query()
            ->select([
                'third_party_wallets.*',

                'uc.name as created_by_name',
            ])
            ->where('tp_id', $this->dto->tp_id)
            ->leftJoin('users as uc', 'uc.id', '=', 'third_party_wallets.created_by');


        Helpers::sortBuilder($query, $dto->toArray(), [

        ]);

        $query->orderBy('third_party_wallets.id', 'DESC');

        return $query->paginate(data_get($dto, 'limit', ITEM_PER_PAGE));
    }
}
