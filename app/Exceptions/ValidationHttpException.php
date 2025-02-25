<?php

namespace App\Exceptions;

use Dingo\Api\Exception\ResourceException;
use Exception;
use Illuminate\Support\Arr;

class ValidationHttpException extends ResourceException
{
    /**
     * ValidationHttpException constructor.
     * @param null $errors
     * @param Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct($errors = null, Exception $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(Arr::first(Arr::first($errors)), $errors, $previous, $headers, $code);
    }
}