<?php

namespace App\Modules\Inventory\Actions;

use App\Entities\Carton;
use App\Entities\Location;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\ExportCustom;
use App\Libraries\Helpers;
use App\Libraries\Language;
use App\Modules\Inventory\DTO\InventoryByLocationDTO;
use App\Modules\Inventory\Transformers\InventoryByLocationTransformer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class InventoryByLocationAction
{
    public InventoryByLocationDTO $dto;

    /**
     * handle
     * @param InventoryByLocationDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = Location::query()
            ->select([
                'locations.loc_id',
                'locations.loc_code',
                'locations.loc_name',
                'cartons.item_id',
                'cartons.bin_loc_id',
                'items.sku',
                'items.item_name',
                'items.m3',
                'bin_locations.code as bin_loc_code',
                'bin_locations.name as bin_loc_name',
                DB::raw('SUM((cartons.ctn_ttl * cartons.piece_init ) + cartons.piece_remain) as total_qty'),
            ])
//            ->withCalcTotalM3()
            ->join('cartons', function ($q) {
                $q->on('cartons.loc_id', '=', 'locations.loc_id')
                    ->where('cartons.whs_id', '=', $this->dto->whs_id)
                    ->where('cartons.ctn_sts', '=', Carton::STS_ACTIVE)
                    ->where('cartons.deleted', '=', 0);
            })
            ->join('items', function ($q) {
                $q->on('items.item_id', '=', 'cartons.item_id')
                    ->where('items.deleted', '=', 0);
            })
            ->join('bin_locations', function ($q) {
                $q->on('bin_locations.id', '=', 'cartons.bin_loc_id')
                    ->where('bin_locations.deleted', '=', 0);
            })
            ->where('locations.whs_id', '=', $dto->whs_id)
            ->groupBy([
                'locations.loc_id',
                'cartons.item_id',
                'cartons.bin_loc_id',
            ]);

        if ($cusId = $dto->cus_id) {
            $query->where('cartons.cus_id', '=', $cusId);
        }

        if ($locCode = $dto->loc_code) {
            $query->where('locations.loc_code', 'LIKE', "%$locCode%");
        }

        if ($sku = $dto->sku) {
            $query->where('items.sku', 'LIKE', "%$sku%");
        }

        if ($binLocId = $dto->bin_loc_id) {
            $query->where('cartons.bin_loc_id', '=', $binLocId);
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'item_id' => 'cartons.item_id',
            'bin_loc_id' => 'cartons.bin_loc_id',
            'sku' => 'items.sku',
            'item_name' => 'items.item_name',
            'm3' => 'items.m3',
            'bin_loc_code' => 'bin_locations.code',
            'bin_loc_name' => 'bin_locations.name',
            'total_qty' => 'total_qty',
            'total_m3' => 'total_m3',
        ], true);

        if ($exportType = data_get($dto, 'export_type')) {
            return $this->handleExport($query);
        }

        $query->orderBy('cartons.updated_at', 'desc')
            ->orderBy('items.sku');

        return $query->paginate(data_get($dto, 'limit') ?? ITEM_PER_PAGE);
    }

    public function handleExport($query)
    {
        $transformer = new InventoryByLocationTransformer();
        $title = $transformer->getTitleExport();

        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);
        $locations = $query->limit($limit)->get();

        $data = $locations->transform(function ($location) use ($transformer, &$grandTotal, &$totalM3) {
            $grandTotal += data_get($location, 'total_qty', 0);
            $totalM3 += data_get($location, 'total_m3', 0);
            return $transformer->transformExport($location);
        })->toArray();

        $listGroupOrders = $this->formatData($data, $title);

        return Excel::download(new ExportCustom([
            'data' => $listGroupOrders,
            'titles' => $title,
            'titleName' => Language::translate('Inventory By Location'),
        ], 'InventoryByLocationExcelTemplate'), "InventoryByLocation.xlsx");
    }

    private static function formatData(array $data, array $title)
    {
        if (empty($data)) {
            return [];
        }

        $dataSave = [];

        foreach ($data as $itmK => $item) {
            $temp = [];

            foreach ($title as $key => $field) {
                $values = explode("|", $key);
                $value = "";

                if (count($values) == 1) {
                    $value = array_get($item, $values[0], null);
                } else if (!empty($values[1])) {
                    switch ($values[1]) {
                        case "*":
                            $value = array_get($item, $values[0], null) * array_get($item, $values[2], null);
                            break;
                        case ".":
                            $value = trim(array_get($item, $values[0], null) . " " .
                                array_get($item, $values[2], null));
                            break;
                        case "format_datetime":
                            $value = Helpers::formatToDateTime(array_get($item, $values[0], null));
                            break;
                        case "format_date":
                            $value = Helpers::formatToDate(array_get($item, $values[0], null));
                            break;
                        case "translate":
                            $value = Language::translate(array_get($item, $values[0], null));
                            break;
                        case "format_string":
                            $value = '="' . array_get($item, $values[0], null);
                            break;
                        case "format_number":
                            $value = Helpers::formatNumber(array_get($item, $values[0], null));
                            break;
                        case "format_number_new":
                            $value = (int)str_replace(',', '', array_get($item, $values[0], null));
                            break;
                        case "format_number_m3":
                            $value = Helpers::formatNumberTotalM3(array_get($item, $values[0], null), data_get($item, 'cus_id'));
                            break;
                    }
                }

                $temp[$field] = $value;
            }

            ++$itmK;
            $dataSave[$itmK] = $temp;
        }

        return $dataSave;
    }

    public function isExport()
    {
        if ($exportType = data_get($this->dto, 'export_type')) {
            return true;
        }

        return false;
    }
}
