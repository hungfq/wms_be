<?php

namespace App\Modules\MasterData\DTO\Autocompletete;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class AutocompleteItemDTO extends FlexibleDataTransferObject
{
    public $search;
    public $limit;
    public $page;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'search' => $request->input('search'),
            'limit' => $request->input('limit'),
            'page' => $request->input('page'),
        ]);
    }
}
