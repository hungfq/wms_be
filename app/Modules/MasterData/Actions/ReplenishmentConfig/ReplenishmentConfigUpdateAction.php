<?php

namespace App\Modules\MasterData\Actions\ReplenishmentConfig;

use App\Entities\Item;
use App\Entities\ReplenishmentConfig;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\DTO\ReplenishmentConfig\ReplenishmentConfigCreateDTO;

class ReplenishmentConfigUpdateAction
{
    public ReplenishmentConfigCreateDTO $dto;
    protected $config;

    /**
     * @param ReplenishmentConfigCreateDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->updateReplenishmentConfig();
    }

    protected function checkData()
    {
        $this->config = ReplenishmentConfig::query()->find($this->dto->id);
        if (!$this->config) {
            throw new UserException(Language::translate('Replenishment Config not found'));
        }

        $isExist = ReplenishmentConfig::query()
            ->where('whs_id', $this->dto->whs_id)
            ->where('id', '<>', $this->dto->id)
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

    protected function updateReplenishmentConfig()
    {
        $this->config->update([
            'item_id' => $this->dto->item_id,
            'min_qty' => $this->dto->min_qty,
            'des' => $this->dto->des,
        ]);

        return $this;
    }
}
