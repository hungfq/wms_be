<?php

namespace App\Modules\MasterData\DTO\ThirdParty;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ThirdPartyStoreDTO extends FlexibleDataTransferObject
{
    public $cus_id;
    public $tp_group_id;
    public $code;
    public $name;
    public $vat_code;
    public $email;
    public $phone;
    public $mobile;
    public $address;
    public $location;
    public $state_id;
    public $city;
    public $zip_code;
    public $fax;
    public $des;
    public $group_channel_ids;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'cus_id' => $request->input('cus_id'),
            'tp_group_id' => $request->input('tp_group_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'vat_code' => $request->input('vat_code'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'mobile' => $request->input('mobile'),
            'address' => $request->input('address'),
            'location' => $request->input('location'),
            'state_id' => $request->input('state_id'),
            'city' => $request->input('city'),
            'zip_code' => $request->input('zip_code'),
            'fax' => $request->input('fax'),
            'des' => $request->input('des'),
            'group_channel_ids' => $request->input('group_channel_ids') ?? [],
        ]);
    }
}
