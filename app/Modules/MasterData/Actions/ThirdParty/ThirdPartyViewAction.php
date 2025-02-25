<?php

namespace App\Modules\MasterData\Actions\ThirdParty;

use App\Entities\ThirdParty;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\Export;
use App\Libraries\Helpers;
use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyViewDTO;
use App\Modules\MasterData\Transformers\ThirdParty\ThirdPartyViewTransformer;

class ThirdPartyViewAction
{
    public ThirdPartyViewDTO $dto;

    /**
     * @param ThirdPartyViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = ThirdParty::query()
            ->select([
                'third_party.*',
                'states.code as state_code',
                'states.name as state_name',
                'areas.id as area_id',
                'areas.code as area_code',
                'areas.name as area_name',
                'countries.id as country_id',
                'countries.code as country_code',
                'countries.name as country_name',
                'customers.code as cus_code',
                'customers.name as cus_name',
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
            ])
            ->leftJoin('states', 'states.id', '=', 'third_party.state_id')
            ->leftJoin('areas', 'areas.id', '=', 'states.area_id')
            ->leftJoin('countries', 'countries.id', '=', 'states.country_id')
            ->join('customers', 'customers.cus_id', 'third_party.cus_id');

        if ($cusId = data_get($dto, 'cus_id')) {
            $query->where('third_party.cus_id', $cusId);
        }

        if ($code = data_get($dto, 'code')) {
            $query->where('third_party.code', 'LIKE', "%{$code}%");
        }

        if ($name = data_get($dto, 'name')) {
            $query->where('third_party.name', 'LIKE', "%{$name}%");
        }

        if ($phone = data_get($dto, 'phone')) {
            $query->where('third_party.phone', 'LIKE', "%{$phone}%");
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'cus_name' => 'customers.name',
            'code' => 'third_party.code',
            'name' => 'third_party.name',
            'phone' => 'third_party.phone',
            'created_date' => 'third_party.created_at',
            'created_by_name' => 'uc.name',
            'updated_date' => 'third_party.updated_at',
            'updated_by_name' => 'uu.name',
            'country_code' => 'countries.code',
            'country_name' => 'countries.name',
            'area_code' => 'areas.code',
            'area_name' => 'areas.name',
            'state_code' => 'states.code',
            'state_name' => 'states.name',
        ]);

        $query->orderBy('third_party.tp_id', 'DESC')
            ->leftJoin('users as uc', 'uc.id', '=', 'third_party.created_by')
            ->leftJoin('users as uu', 'uu.id', '=', 'third_party.updated_by');

        if ($exportType = data_get($dto, 'export_type')) {
            return $this->handleExport($exportType, $query);
        }

        return $query->paginate(data_get($dto, 'limit', ITEM_PER_PAGE));
    }

    public function handleExport($exportType, $query)
    {
        $transformer = new ThirdPartyViewTransformer();
        $title = $transformer->getTitleExport();

        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);
        $thirdParty = $query->limit($limit)->get();

        $data = $thirdParty->map(function ($value, $key) use ($transformer) {
            return $transformer->transform($value);
        })->toArray();

        return Export::export($exportType, $title, $data, 'ThirdPartyExport', 'Third Party List');
    }

    public function isExport()
    {
        if ($exportType = data_get($this->dto, 'export_type')) {
            return true;
        }

        return false;
    }
}
