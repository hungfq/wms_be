<?php

namespace App\Modules\MasterData\DTO\Uom;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UomUpsertDTO extends FlexibleDataTransferObject
{
    public $id;
    public $code;
    public $name;
    public $description;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'id' => $request->input('id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);
    }
}
