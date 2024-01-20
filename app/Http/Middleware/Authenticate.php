<?php

namespace App\Http\Middleware;

use Closure;
use Sentry\State\Scope;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class Authenticate extends BaseMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $this->authenticate($request);

        $user = $this->auth->user();
        if ( $user->status === 'IA' ) {
            return $this->responseError('Your account is inactive, please contact admin!');
        }

//        if (app()->bound('sentry')) {
//            \Sentry\configureScope(function (Scope $scope) use ($user): void {
//                $scope->setUser([
//                    'id' => $user->id,
//                    'email' => $user->email
//                ]);
//
//                $scope->setTag('app_name', env('APP_NAME'));
//            });
//        }

        $requestToken = (string)$this->auth->getToken();
        $payload = $this->auth->getPayload()->toArray();
        if ($user->api_token !== $requestToken
            && !(in_array(data_get($payload, 'token_type'), ['internal', 'impersonate']) && data_get($payload, 'sub') == $user->id)) {
            return $this->responseError('Invalid token!', 401);
        }

        $route = $request->route();
        $permissionName = data_get($route, '1.as');
        $addonPermissions = data_get($route, '1.addon_permissions');
        $userPermissions = $user->getAllPermissions()->pluck('name');

        if (!$permissionName) {
            return $next($request);
        }

        if ($userPermissions->contains($permissionName)) {
            return $next($request);
        }

        if (!$userPermissions->contains($permissionName) && $addonPermissions) {
            $addonPermissions = explode('|', $addonPermissions);
            foreach ($addonPermissions as $addonPermission) {
                if ($userPermissions->contains($addonPermission)) {
                    return $next($request);
                }
            }
        }

        return $this->responseError('Access denies!', 403);
    }

    protected function responseError($message = '', $code = 400)
    {
        $message = $message ?: 'Something went wrong';

        $error = [
            'error' => [
                'message' => $message
            ]
        ];

        return response($error, $code);
    }
}