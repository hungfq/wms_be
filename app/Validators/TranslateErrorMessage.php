<?php

namespace App\Validators;


interface TranslateErrorMessage
{
    public function isTranslateMessage($value): bool;
}