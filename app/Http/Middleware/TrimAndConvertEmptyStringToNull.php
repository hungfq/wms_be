<?php

namespace App\Http\Middleware;

use Closure;

class TrimAndConvertEmptyStringToNull
{
    public function handle($request, Closure $next)
    {
        if (!$input = $request->all()) {
            $input = json_decode($request->getContent(), true);

            $input = is_array($input) ? $input : [$input];
        }

        $input = $this->trimArray($input);

        // $input = array_filter($input, function($value) {
        //     return $value !== '';
        // });

        $input = $this->convertEmptyStringToNull($input);

        $request->replace($input);

        return $next($request);
    }

    function trimArray($input)
    {
        if (!is_array($input)) {
            return trim($input);
        }

        return array_map([$this, 'trimArray'], $input);
    }

    function convertEmptyStringToNull($input)
    {
        if ( !is_array($input) ) {
            if (is_string($input) && $input === '') return null;
            return $input;
        }

        return array_map([$this, 'convertEmptyStringToNull'], $input);
    }
}