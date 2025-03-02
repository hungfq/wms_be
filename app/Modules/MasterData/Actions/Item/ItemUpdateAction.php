<?php

namespace App\Modules\MasterData\Actions\Item;

use App\Entities\Customer;
use App\Entities\Item;
use App\Entities\ItemCategory;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\DTO\Item\ItemUpdateDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemUpdateAction
{
    public $item;

    public ItemUpdateDTO $dto;

    /**
     * @param ItemUpdateDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->findItem()
            ->updateItem();
    }

    protected function checkData()
    {
        $isSkuExists = Item::query()
            ->where(DB::raw('UPPER(sku)'), Str::upper($this->dto->sku))
            ->where('item_id', '!=', $this->dto->item_id)
            ->exists();
        if ($isSkuExists) {
            throw new UserException(Language::translate("Item already exists!"));
        }

        if (isset($this->dto->cat_code)) {
            $isCatCodeExists = ItemCategory::query()->where('code', $this->dto->cat_code)->exists();
            if (!$isCatCodeExists) {
                throw new UserException(Language::translate("Category Code is not exists!"));
            }
        }

        $isCusIdExists = Customer::query()->where('cus_id', $this->dto->cus_id)->exists();
        if (!$isCusIdExists) {
            throw new UserException(Language::translate("Customer is not exists!"));
        }

        return $this;
    }

    protected function findItem()
    {
        $this->item = Item::query()->find($this->dto->item_id);

        if (!$this->item) {
            throw new UserException("Item is not exists!");
        }

        return $this;
    }

    protected function updateItem()
    {
        $this->item->update($this->dto->all());

        return $this;
    }
}
