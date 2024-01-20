<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\CustomerInUser;
use App\Entities\Warehouse;
use App\Http\Controllers\ApiController;
use App\Libraries\Data;

class WarehouseController extends ApiController
{
    public function getAll()
    {
        $query = Warehouse::query()
            ->select([
                "whs_id",
                "code as whs_code",
                "name as whs_name",
                "color",
            ])
            ->where('status', Warehouse::DEFAULT_STS_ACTIVE);

        $warehouseIds = CustomerInUser::query()
            ->where('user_id', Data::getCurrentUser()->id)
            ->get()
            ->pluck('whs_id')
            ->toArray();
        $query->whereIn('whs_id', $warehouseIds);

        return [
            'data' => $query->get()->toArray(),
        ];
    }
}
