<?php

namespace App\Modules\MasterData\Actions\ReplenishmentConfig;

use App\Entities\ReplenishmentConfig;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\Export;
use App\Libraries\Helpers;
use App\Modules\MasterData\DTO\ReplenishmentConfig\ReplenishmentConfigViewDTO;
use App\Modules\MasterData\Transformers\ReplenishmentConfig\ReplenishmentConfigViewTransformer;
use Illuminate\Support\Facades\DB;

class ReplenishmentConfigViewAction
{
    public ReplenishmentConfigViewDTO $dto;

    /**
     * @param ReplenishmentConfigViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = ReplenishmentConfig::query()
            ->select([
                'replenishment_configs.*',
                'items.sku',
                'items.item_name',
                'invt_qty as invt_qty',
                DB::raw("CASE WHEN IFNULL(invt_qty, 0) < replenishment_configs.min_qty THEN 1 ELSE 0 END AS is_warning"),
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
            ])
            ->join('items', function ($q) {
                $q->on('items.item_id', '=', 'replenishment_configs.item_id')
                    ->where('items.deleted', '=', 0);
            })
            ->leftJoin(
                DB::raw("
                    (SELECT SUM(inventory.avail_qty) AS invt_qty, inventory.item_id, inventory.whs_id FROM inventory
                    WHERE inventory.deleted = 0
                    GROUP BY inventory.whs_id, inventory.item_id) invt
                "),
                function ($invt) {
                    $invt->on('invt.item_id', 'replenishment_configs.item_id')
                        ->on('invt.whs_id', 'replenishment_configs.whs_id');
                }
            )
            ->where('replenishment_configs.whs_id', $this->dto->whs_id)
            ->groupBy('replenishment_configs.id');

        if ($sku = $dto->sku) {
            $query->where('items.sku', 'LIKE', "%$sku%");
        }
        if ($item_name = $dto->item_name) {
            $query->where('items.item_name', 'LIKE', "%$item_name%");
        }

        if (($dto->is_warning)) {
            $query->having('is_warning', '=', $dto->is_warning);
        }

        $query->leftJoin('users as uc', 'uc.id', '=', $query->qualifyColumn('created_by'))
            ->leftJoin('users as uu', 'uu.id', '=', $query->qualifyColumn('updated_by'));

        if ($sortActCtn = data_get($this->dto->sort, 'act_ctn')) {
            $query->orderBy('act_ctn', $sortActCtn);
        }

        if ($sortActQty = data_get($this->dto->sort, 'act_qty')) {
            $query->orderBy('act_qty', $sortActQty);
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',
            'sku' => 'items.sku',
            'item_name' => 'items.item_name',
            'invt_qty' => 'invt_qty',
        ]);

        if ($exportType = $dto->export_type) {
            return $this->handleExport($query, $exportType);
        }

        return $query->paginate($dto->limit ?? ITEM_PER_PAGE);
    }

    public function handleExport($query, $exportType)
    {
        $transformer = new ReplenishmentConfigViewTransformer();
        $title = $transformer->getTitle();

        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);
        $rplConfig = $query->limit($limit)->get();

        $data = $rplConfig->map(function ($value, $key) use ($transformer) {
            return $transformer->transform($value);
        })->toArray();

        return Export::export($exportType, $title, $data, 'ReplenishmentConfig', 'Replenishment Config');
    }

    public function isExport()
    {
        if ($exportType = data_get($this->dto, 'export_type')) {
            return true;
        }

        return false;
    }
}
