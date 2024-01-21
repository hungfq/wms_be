<?php


namespace App\Modules\Inbound\Transformers\PO;


use App\Libraries\Config;

class PoShowTransformer extends PoViewTransformer
{
    public function transform($poHdr)
    {
        $transform = parent::transform($poHdr);
        $transform['details'] = [];
//        $transform['ttl_printed_pallets'] = $poHdr->poPallets->where('deleted', 0)->count();
//        $transform['ttl_printed_pallets_rfid'] = $poHdr->poPallets->where('printed_by', PoPallet::PRINTED_BY_ID)->count();
//        $transform['ttl_printed_pallets_qty'] = $poHdr->poPallets->where('printed_by', PoPallet::PRINTED_BY_QTY)->count();
        foreach ($poHdr->poDtls as $poDtl) {
            $poDtl->setAttribute('sum_ttl_ctn', $poDtl->act_ctn_ttl + $poDtl->dmg_ctn_ttl);
            $poDtl->setAttribute('sum_qty', $poDtl->act_qty + $poDtl->dmg_qty);
            $poDtl->vendor_name = data_get($poDtl, 'vendor.name');
            $poDtl->bin_loc_name = data_get($poDtl, 'binLocation.name');
            $detail = $poDtl->toArray();
            $detail['po_dtl_sts_name'] = Config::getStatusName('PO_DTL_STATUS', $detail['po_dtl_sts']);
            $detail['total_m3'] = number_format(data_get($poDtl, 'item.m3') * $poDtl->exp_qty, 7);

            $itemTmp = $poDtl->item->toArray();

            unset(
                $itemTmp['created_by'],
                $itemTmp['updated_by'],
                $itemTmp['created_at'],
                $itemTmp['updated_at'],
                $itemTmp['deleted_at'],
                $itemTmp['status'],
                $itemTmp['deleted']
            );
            $uomItem = [
                'uom_code' => data_get($poDtl, 'item.uom.code'),
                'uom_name' => data_get($poDtl, 'item.uom.name'),
            ];

            unset(
                $detail['item'],
                $itemTmp['uom'],
                $detail['vendor'],
                $detail['bin_location'],
            );

            $transform['details'][] = array_merge($detail, $itemTmp, $uomItem);
        }

        return $transform;
    }
}
