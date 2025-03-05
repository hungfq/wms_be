<?php

namespace App\Modules\WhsConfig\Actions;

use App\Entities\Location;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\WhsConfig\DTO\LocationUpsertDTO;

class LocationCreateAction
{
    public LocationUpsertDTO $dto;
    protected $location;

    /**
     * handle
     *
     * @param LocationUpsertDTO $dto
     * @return void
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $existLocCode = Location::query()
            ->where('whs_id', $dto->whs_id)
            ->where('loc_code', $dto->loc_code)
            ->exists();
        if ($existLocCode) {
            throw new UserException(Language::translate("Location already exists"));
        }

        $existLocCode = Location::query()
            ->where('whs_id', $dto->whs_id)
            ->where('loc_name', $dto->loc_name)
            ->exists();
        if ($existLocCode) {
            throw new UserException(Language::translate("Location already exists"));
        }

        $this->location = Location::query()
            ->create([
                'whs_id' => $dto->whs_id,
                'loc_code' => $dto->loc_code,
                'loc_name' => $dto->loc_name,
                'loc_sts' => $dto->loc_sts,
                'loc_type_id' => $dto->loc_type_id,
                'zone_id' => $dto->zone_id,
                'max_pallet' => $dto->max_pallet,
                'length' => $dto->length,
                'width' => $dto->width,
                'height' => $dto->height,
                'aisle' => $dto->aisle,
                'row' => $dto->row,
                'level' => $dto->level,
                'bin' => $dto->bin,
                'can_mix_sku' => $dto->can_mix_sku ?? 0,
                'des' => $dto->des,
            ]);
    }
}
