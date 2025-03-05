<?php

namespace App\Modules\WhsConfig\Actions;

use App\Entities\Location;
use App\Libraries\Helpers;
use App\Modules\WhsConfig\DTO\LocationViewDTO;

class LocationViewAction
{
    public LocationViewDTO $dto;

    /**
     * @param LocationViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = Location::query()
            ->with([
                'statuses',
            ])
            ->select([
                'locations.*',
                'zones.zone_name',
                'loc_types.loc_type_name',
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
            ])
            ->join('loc_types', 'loc_types.loc_type_id', '=', 'locations.loc_type_id')
            ->join('zones', 'zones.zone_id', '=', 'locations.zone_id')
            ->leftJoin('users AS uc', 'uc.id', '=', 'locations.created_by')
            ->leftJoin('users AS uu', 'uu.id', '=', 'locations.updated_by');

        if ($whsId = $this->dto->whs_id) {
            $query->where('locations.whs_id', $whsId);
        }

        if ($locCode = $this->dto->loc_code) {
            $query->where('locations.loc_code', 'LIKE', "%$locCode%");
        }

        if ($locName = $this->dto->loc_name) {
            $query->where('locations.loc_name', 'LIKE', "%$locName%");
        }

        if ($locSts = $this->dto->loc_sts) {
            $query->where('locations.loc_sts', '=', $locSts);
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',
            'zone_name' => 'zones.zone_name',
            'loc_type_name' => 'loc_types.loc_type_name',
        ]);

        if ($this->dto->export_type) {
            return $this->handleDataExport($query);
        }

        return $query->paginate($dto->limit ?? ITEM_PER_PAGE);
    }
}