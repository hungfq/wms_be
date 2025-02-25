<?php

namespace App\Modules\MasterData\DTO\Autocompletete;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class AutocompleteThirdPartyDTO extends FlexibleDataTransferObject
{
    public $cus_id;
    public $code;
    public $name;
    public $address;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'cus_id' => $request->input('cus_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
        ]);
    }
}
