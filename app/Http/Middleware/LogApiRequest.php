<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogApiRequest
{
    public function handle($request, Closure $next)
    {

        $method = strtoupper($request->getMethod());
        $uri = $request->getPathInfo();
        $bodyAsJson = json_encode($request->except([
            'password',
            'password_confirmation',
        ]));

        $data = "{$method} {$uri} - Body: {$bodyAsJson}";

        Log::info($data);

        return $next($request);
    }
}
