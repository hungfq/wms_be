<?php

namespace App\Modules\MasterData\Actions\Item;


use App\Entities\Item;

class ItemShowAction
{
    public function handle($id)
    {
        $query = Item::query()
            ->select([
                'items.*',
                'uoms.name as uom_name',
                'item_categories.name as cat_name',
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
            ])
            ->leftJoin('uoms', function ($q) {
                $q->on('uoms.id', '=', 'items.uom_id');
            })
            ->leftJoin('item_categories', function ($q) {
                $q->on('item_categories.code', '=', 'items.cat_code');
            })
            ->where('items.item_id', $id);

        $query->leftJoin('users as uc', 'uc.id', '=', $query->qualifyColumn('created_by'))
            ->leftJoin('users as uu', 'uu.id', '=', $query->qualifyColumn('updated_by'));

        return $query->first();
    }
}
