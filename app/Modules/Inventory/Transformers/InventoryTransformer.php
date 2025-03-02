<?php

namespace App\Modules\Inventory\Transformers;

use App\Libraries\Helpers;
use League\Fractal\TransformerAbstract;

class InventoryTransformer extends TransformerAbstract
{
    public function transform($inventoryObj)
    {
        return [
            'invt_id' => data_get($inventoryObj, 'invt_id'),
            'whs_id' => data_get($inventoryObj, 'whs_id'),
            'whs_code' => data_get($inventoryObj, 'whs_code'),
            'whs_name' => data_get($inventoryObj, 'whs_name'),
            'cus_id' => data_get($inventoryObj, 'cus_id'),
            'cus_name' => data_get($inventoryObj, 'cus_name'),
            'cus_code' => data_get($inventoryObj, 'cus_code'),
            'bin_loc_id' => data_get($inventoryObj, 'bin_loc_id'),
            'bin_loc_code' => data_get($inventoryObj, 'bin_loc_code'),
            'bin_loc_name' => data_get($inventoryObj, 'bin_loc_name'),
            'item_id' => data_get($inventoryObj, 'item_id'),
            'item_name' => data_get($inventoryObj, 'item_name'),
            'sku' => data_get($inventoryObj, 'sku'),
            'pack_size' => data_get($inventoryObj, 'pack_size'),
            'lot' => data_get($inventoryObj, 'lot'),
            'm3' => Helpers::formatNumberTotalM3(data_get($inventoryObj, 'm3')),
            'total_m3' => Helpers::formatNumberTotalM3(data_get($inventoryObj, 'total_m3')),
            'ttl' => data_get($inventoryObj, 'ttl'),
            'alloc_qty' => data_get($inventoryObj, 'alloc_qty'),
            'picked_qty' => data_get($inventoryObj, 'picked_qty'),
            'avail_qty' => data_get($inventoryObj, 'avail_qty'),
            'dmg_qty' => data_get($inventoryObj, 'dmg_qty'),
            'replenish_qty' => data_get($inventoryObj, 'replenish_qty'),
            'locked_qty' => data_get($inventoryObj, 'locked_qty'),
            'put_back_qty' => data_get($inventoryObj, 'put_back_qty'),
            'created_at' => data_get($inventoryObj, 'created_at'),
            'updated_at' => data_get($inventoryObj, 'updated_at'),
            'created_by' => data_get($inventoryObj, 'created_by'),
            'updated_by' => data_get($inventoryObj, 'updated_by'),
            'created_by_name' => data_get($inventoryObj, 'created_by_name'),
            'updated_by_name' => data_get($inventoryObj, 'updated_by_name')
        ];
    }

    /**
     * @return array
     */
    public function getTitleExport()
    {
        return [
            'sku|format_string' => 'SKU',
            'item_name' => 'Item Name',
            'bin_loc_name' => 'Bin Location Name',
            'lot|format_string' => 'Batch',
            'm3' => '@M3',
            'total_m3' => '∑M3',
            'pack_size' => 'Pack Size',
            'avail_qty' => 'Available QTY',
            'alloc_qty' => 'Allocated QTY',
            'picked_qty' => 'Picked QTY',
            'replenish_qty' => 'Replenish QTY',
            'put_back_qty' => 'PutBack QTY',
            'locked_qty' => 'Locked QTY',
            'ttl' => 'Total QTY',
            'created_by_name' => 'Created By',
            'updated_by_name' => 'Updated By',
        ];
    }
}
