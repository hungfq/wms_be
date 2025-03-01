<?php

namespace App\Modules\Outbound\Controllers;

use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\Outbound\Actions\WavePickCreateAction;
use App\Modules\Outbound\Validators\WavePickCreateValidator;
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
}
