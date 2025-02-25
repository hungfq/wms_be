<?php

namespace App\Modules\Outbound\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Outbound\Actions\OrderViewInventoryItemAction;
use App\Modules\Outbound\DTO\OrderViewInventoryItemDTO;
use App\Modules\Outbound\Transformers\OrderViewInventoryItemTransformer;

class OrderInventoryController extends ApiController
{
    public function searchItem($whsId, OrderViewInventoryItemAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $items = $action->handle(
            OrderViewInventoryItemDTO::fromRequest()
        );

        return $this->response->collection($items, new OrderViewInventoryItemTransformer());
    }
}
