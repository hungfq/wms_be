<?php

namespace App\Modules\WhsConfig\Actions;

use App\Entities\Location;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\WhsConfig\DTO\LocationUpsertDTO;

class LocationShowAction
{
    public LocationUpsertDTO $dto;
    protected $location;

    /**
     * handle
     *
     * @param LocationUpsertDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->location = Location::query()
            ->where('whs_id', $dto->whs_id)
            ->find($dto->id);
        if (!$this->location) {
            throw new UserException(Language::translate("Location not found"));
        }

        return $this->location;
    }
}
