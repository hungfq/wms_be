<?php

namespace App\Modules\Inbound\Transformers\PO;

use App\Libraries\Language;
use League\Fractal\TransformerAbstract;

class PoViewTransformer extends TransformerAbstract
{
    public function transform($poHdr)
    {
        $total_m3 = 0;
        $total_qty = 0;
        foreach (data_get($poHdr, 'poDtls', []) as $detail) {
            $total_m3 += data_get($detail, 'item.m3', 0) * data_get($detail, 'exp_qty', 0);
            $total_qty += data_get($detail, 'exp_qty', 0);
        }

        $totalModel = $poHdr->poDtls->pluck('item_id')->unique()->count();

        $listGrHdr = [];
        foreach (data_get($poHdr, 'grHdrs', []) as $grHdr) {
            $listGrHdr[] = [
                'gr_hdr_id' => data_get($grHdr, 'gr_hdr_id'),
                'gr_hdr_num' => data_get($grHdr, 'gr_hdr_num'),
            ];
        }
        return [
            'po_hdr_id' => $poHdr->po_hdr_id,
            'whs_id' => $poHdr->whs_id,
            'whs_code' => data_get($poHdr, 'whs_code'),
            'whs_name' => data_get($poHdr, 'whs_name'),
            'cus_id' => $poHdr->cus_id,
            'cus_name' => data_get($poHdr, 'cus_code'),
            'cus_code' => data_get($poHdr, 'cus_name'),
            'po_num' => $poHdr->po_num,
            'list_gr_hdr' => $listGrHdr,
            'list_gr_hdr_num' => $poHdr->list_gr_hdr_num,
            'ref_code' => $poHdr->ref_code,
            'seq' => $poHdr->seq,
            'po_sts' => $poHdr->po_sts,
            'po_type' => $poHdr->po_type,
            'po_type_name' => data_get($poHdr, 'po_type_name'),
            'sts_name' => $poHdr->statuses->sts_name,
            'tran_sts_name' => Language::translate($poHdr->statuses->sts_name),
            'des' => $poHdr->des,
            'expt_date' => $poHdr->expt_date,
            'arrived_date' => $poHdr->arrived_date,
            'created_from' => $poHdr->created_from,
            'from_whs_id' => $poHdr->from_whs_id,
            'from_whs_name' => data_get($poHdr, 'fromWareHouses.name'),
            'to_whs_id' => $poHdr->to_whs_id,
            'to_whs_name' => data_get($poHdr, 'toWareHouses.name'),
            'odr_hdr_id' => $poHdr->odr_hdr_id,
            'do_name' => data_get($poHdr, 'doHdr.cus_po'),
            'container_type_id' => data_get($poHdr, 'containerType.id'),
            'container_type_name' => data_get($poHdr, 'containerType.name'),
            'container_type_code' => data_get($poHdr, 'containerType.code'),
            'container_id' => data_get($poHdr, 'container.ctnr_id'),
            'container_code' => data_get($poHdr, 'container.code'),
            'po_no' => $poHdr->po_no,
            'seal_number' => $poHdr->seal_number,
            'mot' => $poHdr->mot,
            'bl_no' => $poHdr->bl_no,
            'voy_no' => $poHdr->voy_no,
            'vessel' => $poHdr->vessel,
            'departure' => $poHdr->departure,
            'invoice_no' => $poHdr->invoice_no,
            'final_des' => $poHdr->final_des,
            'arrival' => $poHdr->arrival,
            'is_mix_model' => $poHdr->is_mix_model,
            'from_vendor_id' => $poHdr->from_vendor_id,
            'from_vendor_code' => data_get($poHdr, 'fromVendor.code'),
            'from_vendor_name' => data_get($poHdr, 'fromVendor.name'),
            'total_m3' => number_format($total_m3, 7),
            'total_qty' => $total_qty,
            'created_by' => $poHdr->created_by,
            'created_by_name' => data_get($poHdr, 'created_by_name'),
            'updated_by_name' => data_get($poHdr, 'updated_by_name'),
            'updated_by' => $poHdr->updated_by,
            'created_at' => $poHdr->created_at,
            'updated_at' => $poHdr->updated_at,
            'total_model' => $totalModel
        ];
    }

    /**
     * use export list
     * @return array
     */
    public function getTitleExport()
    {
        return [
            'tran_sts_name' => 'Status',
            'po_num' => 'PO #',
            'list_gr_hdr_num' => 'GR #',
            // 'container_code' => 'Container No.',
            // 'seal_number' => 'Seal No.',
            // 'invoice_no' => 'Invoice No.',
            // 'cus_code' => 'Customer Code',
            // 'cus_name' => 'Customer Name',
            'po_type_name' => 'PO Type',
            'po_no' => 'PO No.',
            // 'ref_code' => 'Ref Code',
            // 'mot' => 'MOT',
            // 'bl_no' => 'B/L No.',
            // 'voy_no' => 'Voy No.',
            // 'vessel' => 'Vessel',
            'total_m3' => 'Total M3',
            // 'final_des' => 'Final Destination',
            // 'arrival' => 'Arrival',
            'expt_date|format_date' => 'Expected Date',
            // 'arrived_date|format_date' => 'Arrived Date',
            'from_vendor_name' => 'From Vendor',
            'created_at|format_datetime' => 'Created Date',
            'created_by_name' => 'Created By',
            'updated_at|format_datetime' => 'Updated Date',
            'updated_by_name' => 'Updated By',
        ];
    }
}
