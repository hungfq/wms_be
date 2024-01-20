<?php

namespace App\Modules\Notification\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class NotificationViewDTO extends FlexibleDataTransferObject
{
    public $search;
    public $limit;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'search' => $request->input('search'),
            'limit' => $request->input('limit'),
        ]);
    }
}