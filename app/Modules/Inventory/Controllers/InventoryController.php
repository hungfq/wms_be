<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Inventory\Actions\InventoryAction;
use App\Modules\Inventory\Actions\InventoryByLocationAction;
use App\Modules\Inventory\DTO\InventoryByLocationDTO;
use App\Modules\Inventory\DTO\InventoryDTO;
use App\Modules\Inventory\Transformers\InventoryByLocationTransformer;
use App\Modules\Inventory\Transformers\InventoryTransformer;

class InventoryController extends ApiController
{

    public function viewInventory($whsId, InventoryAction $action, InventoryTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId
        ]);

        $inventory = $action->handle(
            InventoryDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $inventory;
        }

        return $this->response->paginator($inventory, $transformer);
    }

    public function viewInventoryByLocation($whsId, InventoryByLocationAction $action, InventoryByLocationTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $result = $action->handle(
            InventoryByLocationDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $result;
        }

        return $this->response->paginator($result, $transformer);
    }
}
