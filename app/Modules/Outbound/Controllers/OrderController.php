<?php

namespace App\Modules\Outbound\Controllers;

use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\Outbound\Actions\OrderAllocateAction;
use App\Modules\Outbound\Actions\OrderCreateAction;
use App\Modules\Outbound\Actions\OrderShowAction;
use App\Modules\Outbound\Actions\OrderUpdateAction;
use App\Modules\Outbound\Actions\OrderViewAction;
use App\Modules\Outbound\DTO\OrderViewDTO;
use App\Modules\Outbound\Transformers\OrderShowTransformer;
use App\Modules\Outbound\Transformers\OrderViewTransformer;
use App\Modules\Outbound\Validators\OrderAllocateValidator;
use App\Modules\Outbound\Validators\OrderCreateValidator;
use App\Modules\Outbound\Validators\OrderUpdateValidator;
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

    public function show($whsId, $odrHdrId, OrderShowAction $action, OrderShowTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'odr_hdr_id' => $odrHdrId,
        ]);

        $result = $action->handle($odrHdrId);

        return $this->response->item($result, $transformer);
    }

    public function update($whsId, $odrHdrId, OrderUpdateAction $action, OrderUpdateValidator $validator)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'odr_hdr_id' => $odrHdrId
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Update Order successfully!'));
    }

    public function allocateMultiple($whsId, OrderAllocateAction $action, OrderAllocateValidator $validator)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        $action->handle(
            $validator->toDTO()
        );

        return $this->responseSuccess(Language::translate('All order(s) allocated success'));
    }
}
