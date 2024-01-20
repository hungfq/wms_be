<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\User\Actions\UserDeleteAction;
use App\Modules\User\Actions\UserImportAction;
use App\Modules\User\Actions\UserShowAction;
use App\Modules\User\Actions\UserStoreAction;
use App\Modules\User\Actions\UserUpdateAction;
use App\Modules\User\Actions\UserViewAction;
use App\Modules\User\Actions\UserViewStatsAction;
use App\Modules\User\DTO\UserViewDTO;
use App\Modules\User\Transformers\UserShowTransformer;
use App\Modules\User\Transformers\UserViewTransformer;
use App\Modules\User\Validators\UserImportValidator;
use App\Modules\User\Validators\UserStoreValidator;
use App\Modules\User\Validators\UserUpdateValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
{
    public function view(UserViewAction $action, UserViewTransformer $transformer)
    {
        $results = $action->handle(UserViewDTO::fromRequest());

        if ($results instanceof Collection) {
            return $this->response->collection($results, $transformer);
        }

        return $this->response->paginator($results, $transformer);
    }

    public function show($id, UserShowAction $action, UserShowTransformer $transformer)
    {
        $results = $action->handle($id);

        return $this->response->item($results, $transformer);
    }

    public function store( UserStoreValidator $validator, UserStoreAction $action)
    {
        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess();
    }

    public function update($id, UserUpdateValidator $validator, UserUpdateAction $action)
    {
        $this->request->merge([
            'id' => $id
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess();
    }

    public function delete($id, UserDeleteAction $action)
    {

        DB::transaction(function () use ($action, $id) {
            $action->handle($id);
        });

        return $this->responseSuccess();
    }

    public function import(UserImportAction $action, UserImportValidator $validator)
    {
        $validator->validate($this->request->all());

        $result = DB::transaction(function () use ($action, $validator) {
            return $action->handle($validator->toDTO());
        });

        return $result === true ? $this->responseSuccess() : $result;
    }

    public function getStats(UserViewStatsAction $action)
    {
        $results = $action->handle();
        return $results;
//        return $this->response->item($results, $transformer);
    }
}
