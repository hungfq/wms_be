<?php

namespace App\Modules\Outbound\Controllers;

use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\Outbound\Actions\OrderCreateAction;
use App\Modules\Outbound\Actions\OrderViewAction;
use App\Modules\Outbound\DTO\OrderViewDTO;
use App\Modules\Outbound\Transformers\OrderViewTransformer;
use App\Modules\Outbound\Validators\OrderCreateValidator;
use Illuminate\Support\Facades\DB;

class OrderController extends ApiController
{
    public function view($whsId, OrderViewAction $action, OrderViewTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $result = $action->handle(
            OrderViewDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $result;
        }

        return $this->response->paginator($result, $transformer);
    }

    public function store($whsId, OrderCreateAction $action, OrderCreateValidator $validator)
    {
        $this->request->merge([
            'whs_id' => $whsId
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Create Order successfully!'));
    }
}
