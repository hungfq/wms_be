<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\User;
use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Actions\User\UserViewAction;
use App\Modules\MasterData\DTO\User\UserViewDTO;
use App\Modules\MasterData\Transformers\User\UserShowTransformer;
use App\Modules\MasterData\Transformers\User\UserViewTransformer;

class UserController extends ApiController
{
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

//    public function store(ThirdPartyStoreAction $action, ThirdPartyStoreValidator $validator)
//    {
//        $validator->validate($this->request->all());
//
//        DB::transaction(function () use ($action, $validator) {
//            $action->handle(
//                $validator->toDTO()
//            );
//        });
//
//        return $this->responseSuccess(Language::translate('Create Third Party Successfully!'));
//    }

//    public function update($userId, ThirdPartyUpdateAction $action, ThirdPartyUpdateValidator $validator)
//    {
//        $this->request->merge([
//            'tp_id' => $userId,
//        ]);
//
//        $validator->validate($this->request->all());
//
//        DB::transaction(function () use ($action, $validator) {
//            $action->handle(
//                $validator->toDTO()
//            );
//        });
//
//        return $this->responseSuccess(Language::translate('Update Third Party Successfully!'));
//    }
}
