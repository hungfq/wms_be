<?php

namespace App\Http\Controllers;

use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    use Helpers;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function respondWithToken($token)
    {
        $data = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ];

        return response()->json(['data' => $data]);
    }

    protected function responseSuccess($message = null, $code=200)
    {
        $data = [
            'message' => 'Successfully.'
        ];

        if ( $message ) {
            $data = array_merge($data, ['message' => $message]);
        }

        return response()->json(['data' => $data], $code);
    }

    protected function responseError($message = null, $code=400)
    {
        $data = [
            'message' => 'Something went wrong.'
        ];

        if ( $message ) {
            $data = array_merge($data, ['message' => $message]);
        }

        return response()->json(['error' => $data], $code);
    }
}
