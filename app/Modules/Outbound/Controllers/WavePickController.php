<?php

namespace App\Modules\Outbound\Controllers;

use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\Outbound\Actions\WavePickCancelAction;
use App\Modules\Outbound\Actions\WavePickCreateAction;
use App\Modules\Outbound\Actions\WavePickPickingAction;
use App\Modules\Outbound\Actions\WavePickShowAction;
use App\Modules\Outbound\Actions\WavePickSuggestLocationAction;
use App\Modules\Outbound\Actions\WavePickViewAction;
use App\Modules\Outbound\DTO\WavePickSuggestLocationDTO;
use App\Modules\Outbound\Transformers\WavePickShowTransformer;
use App\Modules\Outbound\Transformers\WavePickViewTransformer;
use App\Modules\Outbound\Validators\WavePickCancelValidator;
use App\Modules\Outbound\Validators\WavePickCreateValidator;
use App\Modules\Outbound\Validators\WavePickPickingValidator;
use Illuminate\Support\Facades\DB;

class WavePickController extends ApiController
{
    public function store($whsId, WavePickCreateValidator $validator, WavePickCreateAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Create wave pick successfully.'));
    }

    public function view($whsId, WavePickViewAction $action, WavePickViewTransformer $transformer)
    {
        $this->request->merge(['whs_id' => $whsId]);

        $data = $action->search($this->request);

        return $this->response->paginator($data, $transformer);
    }

    public function show($whsId, $wvHdrId, WavePickShowAction $action, WavePickShowTransformer $transformer)
    {
        $this->request->merge(['whs_id' => $whsId, 'wv_id' => $wvHdrId]);

        $data = $action->handle($whsId, $wvHdrId, $this->request);

        return $this->response->item($data, $transformer);
    }

    public function suggestLocation($whsId, $wvHdrId, $wvDtlId, WavePickSuggestLocationAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'wv_hdr_id' => $wvHdrId,
            'wv_dtl_id' => $wvDtlId,
        ]);

        $locations = $action->handle(
            WavePickSuggestLocationDTO::fromRequest()
        );

        return ['data' => $locations];
    }

    public function pick($whsId, $wvHdrId, $wvDtlId, WavePickPickingAction $action, WavePickPickingValidator $validator)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'wv_hdr_id' => $wvHdrId,
            'wv_dtl_id' => $wvDtlId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($validator, $action) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Wave Detail Pick Successfully'));
    }

    public function cancel($whsId, WavePickCancelValidator $validator, WavePickCancelAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess($action->getMessageSuccess());
    }
}
