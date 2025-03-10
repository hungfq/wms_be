<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\MasterData\Actions\ThirdParty\ThirdPartyShowAction;
use App\Modules\MasterData\Actions\ThirdParty\ThirdPartyStoreAction;
use App\Modules\MasterData\Actions\ThirdParty\ThirdPartyUpdateAction;
use App\Modules\MasterData\Actions\ThirdParty\ThirdPartyViewAction;
use App\Modules\MasterData\DTO\ThirdParty\ThirdPartyViewDTO;
use App\Modules\MasterData\Transformers\ThirdParty\ThirdPartyShowTransformer;
use App\Modules\MasterData\Transformers\ThirdParty\ThirdPartyViewTransformer;
use App\Modules\MasterData\Validators\ThirdParty\ThirdPartyStoreValidator;
use App\Modules\MasterData\Validators\ThirdParty\ThirdPartyUpdateValidator;
use Illuminate\Support\Facades\DB;

class ThirdPartyController extends ApiController
{
    public function view(ThirdPartyViewAction $action, ThirdPartyViewTransformer $transformer)
    {
        $data = $action->handle(
            ThirdPartyViewDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $data;
        }

        return $this->response->paginator($data, $transformer);
    }

    public function show($tpId, ThirdPartyShowAction $action, ThirdPartyShowTransformer $transformer)
    {
        $data = $action->handle($tpId);

        return $this->response->item($data, $transformer);
    }

    public function store(ThirdPartyStoreAction $action, ThirdPartyStoreValidator $validator)
    {
        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Create Third Party Successfully!'));
    }

    public function update($tpId, ThirdPartyUpdateAction $action, ThirdPartyUpdateValidator $validator)
    {
        $this->request->merge([
            'tp_id' => $tpId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Update Third Party Successfully!'));
    }
}
