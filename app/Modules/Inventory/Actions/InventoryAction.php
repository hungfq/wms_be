<?php

namespace App\Modules\Inventory\Actions;

use App\Entities\Inventory;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\Export;
use App\Libraries\Helpers;
use App\Modules\Inventory\DTO\InventoryDTO;
use App\Modules\Inventory\Transformers\InventoryTransformer;
use Illuminate\Support\Facades\DB;

class InventoryAction
{
    public InventoryDTO $dto;

    /**
     * @param InventoryDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = Inventory::query()
            ->select([
                'inventory.*',
                'warehouses.code as whs_code',
                'warehouses.name as whs_name',
                'customers.name as cus_name',
                'customers.code as cus_code',
                'bin_locations.name as bin_loc_name',
                'bin_locations.code as bin_loc_code',
                'items.item_code',
                'items.item_name',
                'items.sku',
                'items.pack_size',
                'items.m3',
                'items.serial',
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
                DB::raw('(items.m3 / items.pack_size) * 
                (inventory.avail_qty + inventory.alloc_qty + inventory.put_back_qty + inventory.locked_qty + inventory.replenish_qty)
                as total_m3')
            ])
            ->join('customers', 'customers.cus_id', '=', 'inventory.cus_id')
            ->join('warehouses', 'warehouses.whs_id', '=', 'inventory.whs_id')
            ->join('items', 'items.item_id', '=', 'inventory.item_id')
            ->leftJoin('bin_locations', 'bin_locations.id', '=', 'inventory.bin_loc_id')
            ->leftJoin('users as uc', 'uc.id', '=', 'inventory.created_by')
            ->leftJoin('users as uu', 'uu.id', '=', 'inventory.updated_by')
            ->where(function ($q) {
                $q->where('inventory.whs_id', $this->dto->whs_id)
                    ->where('inventory.ttl', '>', 0);
            })
            ->groupBy('inventory.invt_id');

        if ($cusId = data_get($this->dto, 'cus_id')) {
            $query->where('inventory.cus_id', $cusId);
        }

        if ($binLocId = data_get($this->dto, 'bin_loc_id')) {
            $query->where('inventory.bin_loc_id', $binLocId);
        }

        if ($sku = data_get($this->dto, 'sku')) {
            $query->where('items.sku', 'LIKE', "%{$sku}%");
        }

        if ($lot = data_get($this->dto, 'lot')) {
            $query->where('inventory.lot', 'LIKE', "%{$lot}%");
        }

        if ($packSize = data_get($this->dto, 'pack_size')) {
            $query->where('items.pack_size', $packSize);
        }

        if ($spcHdlCode = data_get($this->dto, 'spc_hdl_code')) {
            $query->where('items.spc_hdl_code', $spcHdlCode);
        }

        $serial = data_get($this->dto, 'serial');
        if (isset($serial) && ($serial == 1 || $serial == 0)) {
            $query->where('items.serial', $serial);
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'cus_name' => 'customers.name',
            'item_code' => 'items.item_code',
            'item_name' => 'items.item_name',
            'sku' => 'items.sku',
            'bin_loc_code' => 'bin_locations.code',
            'bin_loc_name' => 'bin_locations.name',
            'lot' => 'inventory.lot',
            'pack_size' => 'items.pack_size',
            'serial' => 'items.serial',
            'm3' => 'items.m3',
            'total_m3' => 'total_m3',
            'upc' => 'items.upc',
            'avail_qty' => 'inventory.avail_qty',
            'alloc_qty' => 'inventory.alloc_qty',
            'picked_qty' => 'inventory.picked_qty',
            'locked_qty' => 'inventory.locked_qty',
            'put_back_qty' => 'inventory.put_back_qty',
            'ttl' => 'inventory.ttl',
            'replenish_qty' => 'inventory.replenish_qty',
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',
        ]);

        $query->orderBy('inventory.updated_at', 'DESC');

        if ($exportType = data_get($dto, 'export_type')) {
            return $this->handleExport($exportType, $query);
        }

        return $query->paginate(data_get($dto, 'limit') ?? ITEM_PER_PAGE);
    }

    public function handleExport($exportType, $query)
    {
        $transform = new InventoryTransformer();
        $title = $transform->getTitleExport();

        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);
        $invts = $query->limit($limit)->get();

        $data = [];
        foreach ($invts as $k => $invt) {
            $data[] = $invt->toArray();
        }

        return Export::export($exportType, $title, $data, 'ReportInventory', 'Report Inventory');
    }

    public function isExport()
    {
        if ($exportType = data_get($this->dto, 'export_type')) {
            return true;
        }

        return false;
    }
}
