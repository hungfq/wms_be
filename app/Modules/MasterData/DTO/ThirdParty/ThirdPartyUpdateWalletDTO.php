<?php

namespace App\Modules\MasterData\DTO\ThirdParty;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ThirdPartyUpdateWalletDTO extends FlexibleDataTransferObject
{
    public $tp_id;
    public $amount;
    public $type;
    public $description;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'tp_id' => $request->input('tp_id'),
            'amount' => $request->input('amount'),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
        ]);
    }
}
