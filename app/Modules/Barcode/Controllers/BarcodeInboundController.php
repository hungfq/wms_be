<?php

namespace App\Modules\Barcode\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Barcode\Actions\BarcodeInboundReceiveAction;
use App\Modules\Barcode\Validators\BarcodeInboundReceiveValidator;
use Illuminate\Support\Facades\DB;

class BarcodeInboundController extends ApiController
{
    public function receiveCarton($whsId, BarcodeInboundReceiveAction $action, BarcodeInboundReceiveValidator $validator)
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

        return $this->responseSuccess();
    }
}
