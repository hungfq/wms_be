<?php

namespace App\Modules\Inbound\DTO\PO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class PoStoreDTO extends FlexibleDataTransferObject
{
    public $whs_id;
    public $po_hdr_id;
    public $po_type;
    public $seal_number;
    public $ref_code;
    public $po_no;
    public $expt_date;
    public $container_code;
    public $container_id;
    public $container_type_id;
    public $odr_hdr_id;
    public $from_whs_id;
    public $to_whs_id;
    public $des;
    public $cus_id;
    public $mot;
    public $internal_id;
    public $bl_no;
    public $voy_no;
    public $vessel;
    public $departure;
    public $invoice_no;
    public $from_vendor_id;
    public $final_des;
    public $arrival;
    public $is_mix_model;
    /** @var \App\Modules\Inbound\DTO\PO\PoStoreDetailDTO[] */
    public $details;
    public $po_num;
    public $po_sts;
    public $created_from;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'whs_id' => $request->input('whs_id'),
            'po_hdr_id' => $request->input('po_hdr_id'),
            'po_type' => $request->input('po_type'),
            'seal_number' => $request->input('seal_number'),
            'ref_code' => $request->input('ref_code'),
            'expt_date' => $request->input('expt_date'),
            'po_no' => $request->input('po_no'),
            'container_code' => $request->input('container_code'),
            'container_id' => $request->input('container_id'),
            'container_type_id' => $request->input('container_type_id'),
            'odr_hdr_id' => $request->input('odr_hdr_id'),
            'from_whs_id' => $request->input('from_whs_id'),
            'to_whs_id' => $request->input('to_whs_id'),
            'des' => $request->input('des'),
            'cus_id' => $request->input('cus_id'),
            'mot' => $request->input('mot'),
            'internal_id' => $request->input('internal_id'),
            'bl_no' => $request->input('bl_no'),
            'voy_no' => $request->input('voy_no'),
            'vessel' => $request->input('vessel'),
            'departure' => $request->input('departure'),
            'invoice_no' => $request->input('invoice_no'),
            'from_vendor_id' => $request->input('from_vendor_id'),
            'final_des' => $request->input('final_des'),
            'arrival' => $request->input('arrival'),
            'is_mix_model' => $request->input('is_mix_model'),
            'details' => $request->input('details'),
        ]);
    }
}
