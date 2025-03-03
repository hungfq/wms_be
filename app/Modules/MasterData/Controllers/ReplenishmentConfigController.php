<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Actions\ReplenishmentConfig\ReplenishmentConfigCreateAction;
use App\Modules\MasterData\Actions\ReplenishmentConfig\ReplenishmentConfigDeleteAction;
use App\Modules\MasterData\Actions\ReplenishmentConfig\ReplenishmentConfigShowAction;
use App\Modules\MasterData\Actions\ReplenishmentConfig\ReplenishmentConfigUpdateAction;
use App\Modules\MasterData\Actions\ReplenishmentConfig\ReplenishmentConfigViewAction;
use App\Modules\MasterData\DTO\ReplenishmentConfig\ReplenishmentConfigViewDTO;
use App\Modules\MasterData\Transformers\ReplenishmentConfig\ReplenishmentConfigShowTransformer;
use App\Modules\MasterData\Transformers\ReplenishmentConfig\ReplenishmentConfigViewTransformer;
use App\Modules\MasterData\Validators\ReplenishmentConfig\ReplenishmentConfigCreateValidator;
use Illuminate\Support\Facades\DB;

class ReplenishmentConfigController extends ApiController
{
    public function view($whsId, ReplenishmentConfigViewAction $action, ReplenishmentConfigViewTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId
        ]);

        $results = $action->handle(
            ReplenishmentConfigViewDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $results;
        }

        return $this->response->paginator($results, $transformer);
    }

    public function store($whsId, ReplenishmentConfigCreateAction $action, ReplenishmentConfigCreateValidator $validator)
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

        return $this->responseSuccess();
    }

    public function show($whsId, $reId, ReplenishmentConfigShowAction $action, ReplenishmentConfigShowTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'id' => $reId,
        ]);

        $result = $action->handle($reId);

        return $this->response->item($result, $transformer);
    }

    public function update($whsId, $reId, ReplenishmentConfigUpdateAction $action, ReplenishmentConfigCreateValidator $validator)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'id' => $reId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess();
    }

    public function delete($whsId, $reId, ReplenishmentConfigDeleteAction $action)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'id' => $reId,
        ]);

        DB::transaction(function () use ($action, $reId) {
            $action->handle($reId);
        });

        return $this->responseSuccess();
    }
}
