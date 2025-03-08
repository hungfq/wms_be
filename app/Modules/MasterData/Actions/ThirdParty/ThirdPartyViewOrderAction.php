<?php

namespace App\Modules\MasterData\Actions\ThirdParty;

use App\Entities\OrderHdr;
use App\Libraries\Helpers;
use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyViewOrderDTO;

class ThirdPartyViewOrderAction
{
    public ThirdPartyViewOrderDTO $dto;

    /**
     * @param ThirdPartyViewOrderDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = OrderHdr::query()
            ->select([
                'odr_hdr.*',

                'uc.name as created_by_name',
            ])
            ->where('odr_hdr.tp_id', $this->dto->tp_id)
            ->where('odr_hdr.odr_sts', '<>', OrderHdr::STS_CANCELED)
            ->leftJoin('users as uc', 'uc.id', '=', 'odr_hdr.created_by');


        Helpers::sortBuilder($query, $dto->toArray(), [

        ]);

        $query->orderBy('odr_hdr.id', 'DESC');

        return $query->paginate(data_get($dto, 'limit', ITEM_PER_PAGE));
    }
}
