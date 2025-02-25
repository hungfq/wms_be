<?php

namespace App\Modules\Auth\Controllers;

use App\Entities\Profile;
use App\Http\Controllers\ApiController;
use App\Libraries\Data;
use App\Modules\Auth\Actions\AuthLoginWithGoogleAccessTokenAction;
use App\Modules\Auth\Transformers\UserGetPermissionTransformer;
use App\Modules\Auth\Transformers\UserGetProfileTransformer;
use App\Modules\Auth\Validators\AuthLoginUserPassValidator;
use App\Modules\Auth\Validators\AuthLoginWithGoogleAccessTokenValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AuthController extends ApiController
{
    public function loginWithGoogleAccessToken(AuthLoginWithGoogleAccessTokenValidator $validator,
                                               AuthLoginWithGoogleAccessTokenAction    $action)
    {
        $validator->validate($this->request->all());

        $result = DB::transaction(function () use ($action, $validator) {
            return $action->handle($validator->toDTO());
        });

        return response()->json($result);
    }

    public function loginWithUserPass(AuthLoginUserPassValidator $validator)
    {
        $validator->validate($this->request->all());

        $fieldType = filter_var($validator->toDTO()->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $fieldType => $validator->toDTO()->email,
            'password' => $validator->toDTO()->password
        ];

        if (!$token = Auth::attempt($credentials)) {
            return $this->responseError(trans('Incorrect email or password!'), 401);
        }

        $user = Auth::user();

        if (data_get($user, 'status') === 'IA') {
            return $this->responseError(trans('Your account is inactive. Please contact admin for support!'), 401);
        }

        $user->update(['api_token' => $token]);

        return $this->respondWithToken($token);
    }

    public function getProfile()
    {
        $user = Auth::user()->load(['profile']);

        if ($info = Data::getUserCache()) {
            $info['warehouse'] = Data::getCurrentWarehouse();
            $info['theme_code'] = Data::getUserCache('theme_code') ?? 'theme_light';
            $info['menu_type'] = Data::getUserCache('menu_type') ?? 'menu_type_tree';
            $info['language'] = Data::getUserCache('last_language') ?? 'vi';
            $info['pallet_prefixs'] = Data::getUserCache('pallet_prefixs') ?? $this->getPalletPrefixs();
            $info['selected_cus_id'] = data_get($info, 'selected_cus_id') ? (int)data_get($info, 'selected_cus_id') : null;
            $info['selected_cus_name'] = data_get($info, 'selected_cus_name');

            $user->info = $info;

            Data::setOutInfoData();

            return $this->response->item($user, new UserGetProfileTransformer);
        }

        Data::setUserInfo();

        $info = Data::getUserCache();

        $info['warehouse'] = Data::getCurrentWarehouse();
        $info['theme_code'] = Data::getUserCache('theme_code') ?? 'theme_light';
        $info['menu_type'] = Data::getUserCache('menu_type') ?? 'menu_type_tree';
        $info['language'] = Data::getUserCache('last_language') ?? 'vi';
        $info['pallet_prefixs'] = Data::getUserCache('pallet_prefixs') ?? $this->getPalletPrefixs();
        $info['selected_cus_id'] = data_get($info, 'selected_cus_id') ? (int)data_get($info, 'selected_cus_id') : null;
        $info['selected_cus_name'] = data_get($info, 'selected_cus_name');

        $user->info = $info;

        Data::setOutInfoData();

        return $this->response->item($user, new UserGetProfileTransformer);
    }

    public function getPermissions()
    {
        $user = Auth::user();

        $permissions = $user->getAllPermissions()->sortBy('name');

        return $this->response->collection($permissions, new UserGetPermissionTransformer);
    }

    public function setInfo(Request $request)
    {
        $info = Data::getUserCache();

        if ($info) {
            $info = array_merge($info, $request->input('info'));
        }

        Data::setUserCache($info);

        if ($whsId = $request->input('info.whs_id')) {
            Data::setUserCache($whsId, 'last_whs_id');
        }

        if ($language = $request->input('info.language')) {
            Data::setUserCache($language, 'last_language');
        }

        if ($themeCode = $request->input('info.theme_code')) {
            Data::setUserCache($themeCode, 'theme_code');
        }

        if ($menuType = $request->input('info.menu_type')) {
            Data::setUserCache($menuType, 'menu_type');
        }

        return $this->responseSuccess();
    }

    public function logout()
    {
        $user = Auth::user();

        $user->update([
            'api_token' => NULL
        ]);

        $data = [
            'message' => trans('Log out successfully!')
        ];

        Data::clearUserCache();

        return ['data' => $data];
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $attributes = $this->validate($this->request, [
            'gender' => 'required|in:MALE,FEMALE',
            'first_name' => 'required',
            'last_name' => 'required',
            'image_url' => 'nullable',
            'image' => 'nullable|image|max:10240',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|min:10',
            'user_name' => 'required|unique:users,name,' . $user->id
        ]);

        if ( $gender = $this->request->input('gender') ) {
            $attributes['gender'] = Str::upper($gender);
        }

        $attributes['full_name'] = data_get($attributes, 'first_name') . ' ' . data_get($attributes, 'last_name');

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            $attributes
        );

        $user->update([
            'name' => data_get($attributes, 'user_name')
        ]);

        return $this->responseSuccess();
    }

    protected function getPalletPrefixs()
    {
        $palletPrefixs = DB::connection('mysql')
            ->table('pallet_prefixes')
            ->select([
                'code'
            ])
            ->get();

        return $palletPrefixs->toArray();
    }
}
