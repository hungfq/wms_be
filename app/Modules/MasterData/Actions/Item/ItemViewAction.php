<?php

namespace App\Modules\MasterData\Actions\Item;

use App\Entities\Item;
use App\Entities\Statuses;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\Export;
use App\Libraries\Helpers;
use App\Modules\MasterData\DTO\Item\ItemViewDTO;
use App\Modules\MasterData\Transformers\Item\ItemViewTransformer;

class ItemViewAction
{
    public ItemViewDTO $dto;

    /**
     * @param ItemViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = Item::query()
            ->select([
                'items.*',
                'sts.sts_name as status_name',
                'uoms.name as uom_name',
                'item_categories.name as cat_name',
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
            ])
            ->leftJoin('statuses as sts', function ($q) {
                $q->on("sts.sts_code", '=', 'items.status')
                    ->where("sts.sts_type", '=', Statuses::STATUS_ITEM_TYPE);
            })
            ->leftJoin('uoms', function ($q) {
                $q->on('uoms.id', '=', 'items.uom_id');
            })
            ->leftJoin('item_categories', function ($q) {
                $q->on('item_categories.code', '=', 'items.cat_code');
            });

        if ($cusId = $dto->cus_id) {
            $query->where('items.cus_id', '=', $cusId);
        }

        if ($sku = $dto->sku) {
            $query->where('items.sku', 'LIKE', "%$sku%");
        }

        if ($itemName = $dto->item_name) {
            $query->where('items.item_name', 'LIKE', "%$itemName%");
        }

        if ($packSize = $dto->pack_size) {
            $query->where('items.pack_size', '=', $packSize);
        }

        if ($uomId = $dto->uom_id) {
            $query->where('items.uom_id', '=', $uomId);
        }

        if ($catCode = $dto->cat_code) {
            $query->where('items.cat_code', '=', $catCode);
        }

        if ($itemClassId = $dto->item_class_id) {
            $query->where('items.item_class_id', '=', $itemClassId);
        }

        if ($itemStatusId = $dto->item_status_id) {
            $query->where('items.item_status_id', '=', $itemStatusId);
        }

        $query->leftJoin('users as uc', 'uc.id', '=', $query->qualifyColumn('created_by'))
            ->leftJoin('users as uu', 'uu.id', '=', $query->qualifyColumn('updated_by'));

        Helpers::sortBuilder($query, $dto->toArray(), [
            'sts_code' => 'sts.sts_code',
            'status_name' => 'sts.sts_name',
            'uom_name' => 'uoms.name',
            'cat_name' => 'item_categories.name',
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',
        ]);

        if ($exportType = data_get($dto, 'export_type')) {
            return $this->handleExport($exportType, $query);
        }

        if ($dto->limit) {
            return $query->paginate($dto->limit);
        }

        return $query->get();
    }

    public function handleExport($exportType, $query)
    {
        $transformer = new ItemViewTransformer();
        $title = $transformer->getTitleExport();

        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);
        $items = $query->limit($limit)->get();

        $data = $items->map(function ($value, $key) use ($transformer) {
            return $transformer->transform($value);
        })->toArray();

        return Export::export($exportType, $title, $data, 'ItemExport', 'Item List');
    }

    public function isExport()
    {
        if ($exportType = data_get($this->dto, 'export_type')) {
            return true;
        }

        return false;
    }
}
