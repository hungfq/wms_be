<?php

namespace App\Modules\MasterData\Actions\Uom;

use App\Entities\Uom;
use App\Libraries\Helpers;
use App\Modules\MasterData\DTO\Uom\UomViewDTO;

class UomViewAction
{
    public UomViewDTO $dto;

    /**
     * @param UomViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = Uom::query()
            ->select([
                'uoms.*',
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
            ])
            ->leftJoin('users as uc', 'uc.id', '=', 'uoms.created_by')
            ->leftJoin('users as uu', 'uu.id', '=', 'uoms.updated_by');

        if ($cusId = data_get($dto, 'cus_id')) {
            $query->where('uoms.cus_id', $cusId);
        }

        if ($code = data_get($dto, 'code')) {
            $query->where('uoms.code', 'LIKE', "%{$code}%");
        }

        if ($name = data_get($dto, 'name')) {
            $query->where('uoms.name', 'LIKE', "%{$name}%");
        }

        if ($phone = data_get($dto, 'phone')) {
            $query->where('uoms.phone', 'LIKE', "%{$phone}%");
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'created_at' => 'uoms.created_at',
            'created_by_name' => 'uc.name',
            'updated_at' => 'uoms.updated_at',
            'updated_by_name' => 'uu.name',
        ]);

        return $query->paginate(data_get($dto, 'limit', ITEM_PER_PAGE));
    }
}
