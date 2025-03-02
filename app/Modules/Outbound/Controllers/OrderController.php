<?php

namespace App\Modules\Outbound\Controllers;

use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\Outbound\Actions\OrderAllocateAction;
use App\Modules\Outbound\Actions\OrderCancelAction;
use App\Modules\Outbound\Actions\OrderCreateAction;
use App\Modules\Outbound\Actions\OrderOutSortAction;
use App\Modules\Outbound\Actions\OrderRevertAction;
use App\Modules\Outbound\Actions\OrderScheduleToShipAction;
use App\Modules\Outbound\Actions\OrderShipAction;
use App\Modules\Outbound\Actions\OrderShowAction;
use App\Modules\Outbound\Actions\OrderUpdateAction;
use App\Modules\Outbound\Actions\OrderUpdateRemarkAction;
use App\Modules\Outbound\Actions\OrderViewAction;
use App\Modules\Outbound\DTO\OrderUpdateRemarkDTO;
use App\Modules\Outbound\DTO\OrderViewDTO;
use App\Modules\Outbound\Transformers\OrderShowTransformer;
use App\Modules\Outbound\Transformers\OrderViewTransformer;
use App\Modules\Outbound\Validators\OrderAllocateValidator;
use App\Modules\Outbound\Validators\OrderCancelValidator;
use App\Modules\Outbound\Validators\OrderCreateValidator;
use App\Modules\Outbound\Validators\OrderOutSortValidator;
use App\Modules\Outbound\Validators\OrderRevertValidator;
use App\Modules\Outbound\Validators\OrderScheduleToShipValidator;
use App\Modules\Outbound\Validators\OrderShipValidator;
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

    public function outSortMultiple($whsId, OrderOutSortAction $action, OrderOutSortValidator $validator)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        $action->handle(
            $validator->toDTO()
        );

        return $this->responseSuccess(Language::translate('Confirm Out Sort Successfully!'));
    }

    public function updateRemark($odrHdrId, OrderUpdateRemarkAction $action)
    {
        $this->request->merge([
            'odr_hdr_id' => $odrHdrId
        ]);

        $action->handle(
            OrderUpdateRemarkDTO::fromRequest()
        );

        return $this->responseSuccess(Language::translate('Update Remark Order Successfully.'));
    }

    public function scheduleToShip($whsId, OrderScheduleToShipValidator $validator, OrderScheduleToShipAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($validator, $action) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Schedule To Ship Order Successfully.'));
    }

    public function ship($whsId, OrderShipValidator $validator, OrderShipAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        $action->handle(
            $validator->toDTO()
        );

        return $this->responseSuccess(Language::translate('Ship Order(s) Successfully.'));
    }

    public function revert($whsId, OrderRevertValidator $validator, OrderRevertAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($validator, $action) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Revert Order(s) Successfully.'));
    }

    public function cancel($whsId, OrderCancelValidator $validator, OrderCancelAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($validator, $action) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Cancel Order(s) Successfully.'));
    }
}
