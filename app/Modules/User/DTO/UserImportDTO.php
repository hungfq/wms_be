<?php

namespace App\Modules\User\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UserImportDTO extends FlexibleDataTransferObject
{
    public $file;
    public $type;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'file' => $request->file('file'),
            'type' => $request->input('type'),
        ]);
    }
}