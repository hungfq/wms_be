<?php

namespace App\Modules\MasterData\Validators\Item;

use App\Modules\MasterData\DTO\Item\ItemUpdateDTO;
use App\Validators\AbstractValidator;

class ItemUpdateValidator extends AbstractValidator
{
    public function rules($params = [])
    {
        return [
            'sku' => 'required|string',
            'item_name' => 'required|string',
            'pack_size' => 'required|integer|min:1',
            'serial' => 'required|in:0,1',
            'cus_id' => 'required|integer|min:1',
            'uom_id' => 'required|integer|min:1',
            'des' => 'nullable|string',
            'cat_code' => 'nullable|string',
            'status' => 'required|in:AC,IA',
            'm3' => 'nullable|numeric',
            'price_suggest' => 'nullable|numeric',
        ];
    }

    public function toDTO()
    {
        return ItemUpdateDTO::fromRequest();
    }
}
