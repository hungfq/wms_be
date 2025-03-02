<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Actions\Item\ItemShowAction;
use App\Modules\MasterData\Actions\Item\ItemStoreAction;
use App\Modules\MasterData\Actions\Item\ItemUpdateAction;
use App\Modules\MasterData\Actions\Item\ItemViewAction;
use App\Modules\MasterData\DTO\Item\ItemViewDTO;
use App\Modules\MasterData\Transformers\Item\ItemShowTransformer;
use App\Modules\MasterData\Transformers\Item\ItemViewTransformer;
use App\Modules\MasterData\Validators\Item\ItemStoreValidator;
use App\Modules\MasterData\Validators\Item\ItemUpdateValidator;
use Illuminate\Support\Facades\DB;

class ItemController extends ApiController
{
    public function view(ItemViewAction $action, ItemViewTransformer $transformer)
    {
        $results = $action->handle(
            ItemViewDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $results;
        }

        return $this->response->paginator($results, $transformer);
    }

    public function store(ItemStoreAction $action, ItemStoreValidator $validator)
    {
        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess();
    }

    public function show($itemId, ItemShowAction $action, ItemShowTransformer $transformer)
    {
        $result = $action->handle($itemId);

        return $this->response->item($result, $transformer);
    }

    public function update($itemId, ItemUpdateAction $action, ItemUpdateValidator $validator)
    {
        $this->request->merge([
            'item_id' => $itemId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess();
    }
}
