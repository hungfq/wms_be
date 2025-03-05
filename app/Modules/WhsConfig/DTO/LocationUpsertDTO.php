<?php

namespace App\Modules\WhsConfig\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class LocationUpsertDTO extends FlexibleDataTransferObject
{
    public $id;
    public $whs_id;
    public $loc_code;
    public $loc_name;
    public $loc_sts;
    public $loc_type_id;
    public $zone_id;
    public $max_pallet;
    public $length;
    public $width;
    public $height;
    public $aisle;
    public $row;
    public $level;
    public $bin;
    public $can_mix_sku;
    public $des;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'id' => $request->input('id'),
            'whs_id' => $request->input('whs_id'),
            'loc_code' => $request->input('loc_code'),
            'loc_name' => $request->input('loc_name'),
            'loc_sts' => $request->input('loc_sts'),
            'loc_type_id' => $request->input('loc_type_id'),
            'zone_id' => $request->input('zone_id'),
            'max_pallet' => $request->input('max_pallet'),
            'length' => $request->input('length'),
            'width' => $request->input('width'),
            'height' => $request->input('height'),
            'aisle' => $request->input('aisle'),
            'row' => $request->input('row'),
            'level' => $request->input('level'),
            'bin' => $request->input('bin'),
            'can_mix_sku' => $request->input('can_mix_sku'),
            'des' => $request->input('des'),
        ]);
    }
}