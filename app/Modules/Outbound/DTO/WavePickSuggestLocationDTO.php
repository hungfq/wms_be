<?php

namespace App\Modules\Outbound\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class WavePickSuggestLocationDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $wv_hdr_id;
    public $wv_dtl_id;
    public $ttl_qty;
    public $loc_code;
    public $except_loc_codes;
    public $plt_num;
    public $except_plt_nums;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'wv_hdr_id' => $request->input('wv_hdr_id'),
            'wv_dtl_id' => $request->input('wv_dtl_id'),
            'ttl_qty' => $request->input('ttl_qty'),
            'loc_code' => $request->input('loc_code'),
            'except_loc_codes' => $request->input('except_loc_codes'),
            'plt_num' => $request->input('plt_num'),
            'except_plt_nums' => $request->input('except_plt_nums'),
        ]);
    }
}