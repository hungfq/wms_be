<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\User;
use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Actions\User\UserStoreAction;
use App\Modules\MasterData\Actions\User\UserUpdateAction;
use App\Modules\MasterData\Actions\User\UserViewAction;
use App\Modules\MasterData\DTO\User\UserViewDTO;
use App\Modules\MasterData\Transformers\User\UserShowTransformer;
use App\Modules\MasterData\Transformers\User\UserViewTransformer;
use App\Modules\MasterData\Validators\User\UserStoreValidator;
use App\Modules\MasterData\Validators\User\UserUpdateValidator;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
{
    const DEFAULT_CUS_ID = 1;
    public function view(UserViewAction $action, UserViewTransformer $transformer)
    {
        $data = $action->handle(
            UserViewDTO::fromRequest()
        );

        return $this->response->paginator($data, $transformer);
    }

    public function show($userId, UserShowTransformer $transformer)
    {
        $data = User::query()->find($userId);

        return $this->response->item($data, $transformer);
    }

    public function store(UserStoreAction $action, UserStoreValidator $validator)
    {
        $validator->validate($this->request->all());

        DB::transaction(function () use ($action, $validator) {
            $action->handle(
                $validator->toDTO()
            );
        });

        return $this->responseSuccess();
    }

    public function update($userId, UserUpdateAction $action, UserUpdateValidator $validator)
    {
        $this->request->merge([
            'id' => $userId,
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
