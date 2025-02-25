<?php

namespace App\Modules\Inbound\Controllers;

use App\Entities\PoHdr;
use App\Exceptions\UserException;
use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\Inbound\Actions\PO\PoStoreAction;
use App\Modules\Inbound\Actions\PO\PoUpdateAction;
use App\Modules\Inbound\Actions\PO\PoViewAction;
use App\Modules\Inbound\DTO\PO\PoViewDTO;
use App\Modules\Inbound\Transformers\PO\PoShowTransformer;
use App\Modules\Inbound\Transformers\PO\PoViewTransformer;
use App\Modules\Inbound\Validators\PO\PoStoreValidator;
use App\Modules\Inbound\Validators\PO\PoUpdateValidator;
use Illuminate\Support\Facades\DB;

class PoController extends ApiController
{
    public function index($whsId, PoViewAction $action, PoViewTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $result = $action->handle(
            PoViewDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $result;
        }

        return $this->response->paginator($result, $transformer);
    }

    public function store($whsId, PoStoreValidator $validator, PoStoreAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle($validator->toDTO());
        });

        return $this->responseSuccess(Language::translate('Create PO Successfully.'));
    }

    public function show($whsId, $poHdrId, PoShowTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $poHdr = PoHdr::query()
            ->with([

            ])
            ->find($poHdrId);

        if (!$poHdr) {
            throw new UserException(Language::translate('PO not found!'));
        }

        return $this->response->item($poHdr, $transformer);
    }

    public function update($whsId, $poHdrId, PoUpdateValidator $validator, PoUpdateAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'po_hdr_id' => $poHdrId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle($validator->toDTO());
        });

        return $this->responseSuccess(Language::translate('Update PO Successfully.'));
    }
}
