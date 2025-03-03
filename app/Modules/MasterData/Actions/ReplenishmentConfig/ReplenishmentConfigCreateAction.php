<?php

namespace App\Modules\MasterData\Actions\ReplenishmentConfig;

use App\Entities\Item;
use App\Entities\ReplenishmentConfig;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\DTO\ReplenishmentConfig\ReplenishmentConfigCreateDTO;

class ReplenishmentConfigCreateAction
{
    public ReplenishmentConfigCreateDTO $dto;

    /**
     * @param ReplenishmentConfigCreateDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->createReplenishmentConfig();
    }

    protected function checkData()
    {
        $isExist = ReplenishmentConfig::where('whs_id', $this->dto->whs_id)
            ->where('item_id', $this->dto->item_id)
            ->exists();

        if ($isExist) {
            throw new UserException(Language::translate('Replenishment Config already exists'));
        }

        $item = Item::query()->find($this->dto->item_id);
        if (!$item) {
            throw new UserException(Language::translate('Item not found'));
        }

        return $this;
    }

    protected function createReplenishmentConfig()
    {
        ReplenishmentConfig::create($this->dto->all());

        return $this;
    }
}
