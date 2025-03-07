<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\MasterData\Actions\Uom\UomShowAction;
use App\Modules\MasterData\Actions\Uom\UomStoreAction;
use App\Modules\MasterData\Actions\Uom\UomUpdateAction;
use App\Modules\MasterData\Actions\Uom\UomViewAction;
use App\Modules\MasterData\DTO\Uom\UomViewDTO;
use App\Modules\MasterData\Transformers\Uom\UomShowTransformer;
use App\Modules\MasterData\Transformers\Uom\UomViewTransformer;
use App\Modules\MasterData\Validators\Uom\UomStoreValidator;
use App\Modules\MasterData\Validators\Uom\UomUpdateValidator;
use Illuminate\Support\Facades\DB;

class UomController extends ApiController
{
    public function view(UomViewAction $action, UomViewTransformer $transformer)
    {
        $data = $action->handle(
            UomViewDTO::fromRequest()
        );

        return $this->response->paginator($data, $transformer);
    }

    public function show($id, UomShowAction $action, UomShowTransformer $transformer)
    {
        $data = $action->handle($id);

        return $this->response->item($data, $transformer);
    }

    public function store(UomStoreAction $action, UomStoreValidator $validator)
    {
        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Create Uom Successfully!'));
    }

    public function update($id, UomUpdateAction $action, UomUpdateValidator $validator)
    {
        $this->request->merge([
            'id' => $id,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess(Language::translate('Update Uom Successfully!'));
    }
}
