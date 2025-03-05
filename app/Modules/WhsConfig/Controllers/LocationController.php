<?php

namespace App\Modules\WhsConfig\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\WhsConfig\Actions\LocationCreateAction;
use App\Modules\WhsConfig\Actions\LocationDeleteAction;
use App\Modules\WhsConfig\Actions\LocationPickingAction;
use App\Modules\WhsConfig\Actions\LocationShowAction;
use App\Modules\WhsConfig\Actions\LocationSuggestLocationAction;
use App\Modules\WhsConfig\Actions\LocationUpdateAction;
use App\Modules\WhsConfig\Actions\LocationViewAction;
use App\Modules\WhsConfig\DTO\LocationSuggestLocationDTO;
use App\Modules\WhsConfig\DTO\LocationUpsertDTO;
use App\Modules\WhsConfig\DTO\LocationViewDTO;
use App\Modules\WhsConfig\Transformers\LocationShowTransformer;
use App\Modules\WhsConfig\Transformers\LocationViewTransformer;
use App\Modules\WhsConfig\Validators\LocationCancelValidator;
use App\Modules\WhsConfig\Validators\LocationPickingValidator;
use App\Modules\WhsConfig\Validators\LocationUpsertValidator;
use Illuminate\Support\Facades\DB;

class LocationController extends ApiController
{
    public function view($whsId, LocationViewAction $action, LocationViewTransformer $transformer)
    {
        $this->request->merge(['whs_id' => $whsId]);

        $data = $action->handle(LocationViewDTO::fromRequest());

        return $this->response->paginator($data, $transformer);
    }

    public function store($whsId, LocationUpsertValidator $validator, LocationCreateAction $action)
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

    public function show($whsId, $locId, LocationShowAction $action, LocationShowTransformer $transformer)
    {
        $this->request->merge(['whs_id' => $whsId, 'id' => $locId]);

        $data = $action->handle(LocationUpsertDTO::fromRequest());

        return $this->response->item($data, $transformer);
    }

    public function update($whsId, $locId, LocationUpdateAction $action, LocationUpsertValidator $validator)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'id' => $locId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($validator, $action) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess();
    }

    public function delete($whsId, LocationDeleteAction $action)
    {
        DB::transaction(function () use ($action, $whsId) {
            $action->handle($whsId, $this->request->all());
        });

        return $this->responseSuccess();
    }
}
