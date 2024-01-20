<?php

namespace App\Modules\Template\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class TemplateDownloadDTO extends FlexibleDataTransferObject
{
    public $type;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'type' => $request->input('type'),
        ]);
    }
}