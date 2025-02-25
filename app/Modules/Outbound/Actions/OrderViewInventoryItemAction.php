<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\Inventory;
use App\Entities\Item;
use App\Libraries\Config;
use App\Modules\Outbound\DTO\OrderViewInventoryItemDTO;

class OrderViewInventoryItemAction
{
    public OrderViewInventoryItemDTO $dto;
    public $items;
    public $invtCollect;

    /**
     * @param OrderViewInventoryItemDTO $dto
     * @return \Illuminate\Support\Collection
     */
    public function handle($dto)
    {
        $this->dto = $dto;
        $this->invtCollect = collect([]);

        $this->queryItemsOfCustomer()
            ->queryInventoryForItem();

        return $this->invtCollect;
    }

    public function queryItemsOfCustomer()
    {
        $query = Item::query()
            ->select([
                'items.item_id',
                'items.sku',
                'items.size',
                'items.color',
                'items.pack_size',
                'items.serial',
                'items.m3',
            ])
            ->where('items.status', Item::STATUS_ACTIVE)
            ->whereHas('inventories', function ($q) {
                $q->where('whs_id', $this->dto->whs_id);
                $q->where('avail_qty', '>', 0);
            });

        if ($cusId = data_get($this->dto, 'cus_id')) {
            $query->where('items.cus_id', $cusId);
        }

        if ($model = data_get($this->dto, 'sku')) {
            $query->where('items.sku', "LIKE", "%{$model}%");
        }

        $this->items = $query->limit(data_get($this->dto, 'limit', ITEM_PER_PAGE))->get();

        return $this;
    }

    public function queryInventoryForItem($arrIvt = [])
    {
        $binLocId = data_get($this->dto, 'bin_loc_id');
        $itemIds = $this->items->pluck('item_id')->toArray();

        if (!empty($itemIds)) {

            $inventories = Inventory::query()
                ->with(['item'])
                ->where('inventory.whs_id', $this->dto->whs_id)
                ->where('inventory.cus_id', $this->dto->cus_id)
                ->whereIn('inventory.item_id', $itemIds)
                ->where('inventory.avail_qty', '>', 0)
                ->when($binLocId, function ($q) use ($binLocId) {
                    $q->where('bin_loc_id', $binLocId);
                })
                ->get();

            $inventories->groupBy('item_id')->each(function ($invtGroup) {
                $invt = new Inventory();
                $firstInvt = $invtGroup->first();

                $binLocIds = $invtGroup->pluck('bin_loc_id')->unique()->filter();
                foreach ($binLocIds as $binLocId) {
                    $lots[] = [
                        'lot' => Config::ANY,
                        'bin_loc_id' => $binLocId,
                        'pickable_qty' => 0
                    ];
                }

                $anyAvail = [];
                foreach ($invtGroup as $invt) {
                    if (!isset($anyAvail[$invt->bin_loc_id])) {
                        $anyAvail[$invt->bin_loc_id] = $invt->avail_qty;
                    } else {
                        $anyAvail[$invt->bin_loc_id] += $invt->avail_qty;
                    }

                    $lots[] = [
                        'lot' => $invt->lot,
                        'bin_loc_id' => $invt->bin_loc_id,
                        'pickable_qty' => $invt->avail_qty,
                    ];
                }

                foreach ($lots as &$lot) {
                    if ($lot['lot'] === Config::ANY && $lot['bin_loc_id']) {
                        $lot['pickable_qty'] = $anyAvail[$lot['bin_loc_id']];
                    }
                }

                $invt->setAttribute('lots', $lots);
                $invt->fill([
                    'item_id' => $firstInvt->item_id,
                    'sku' => $firstInvt->sku,
                    'pack_size' => $firstInvt->pack_size,
                    'serial' => $firstInvt->serial,
                    'm3' => $firstInvt->m3,
                    'item' => $firstInvt->item
                ]);

                $this->invtCollect->push($invt);
            });
        }

        return $this;
    }
}
