<?php

namespace App\Modules\Inbound\DTO\Autocomplete;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class AutocompleteVendorDTO extends FlexibleDataTransferObject
{
    public $code;
    public $name;
    public $limit;
    public $page;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
        ]);
    }
}
