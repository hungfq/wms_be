<?php

namespace App\Modules\Outbound\Transformers;

use App\Entities\OrderHdr;
use App\Libraries\Config;
use App\Libraries\Helpers;
use League\Fractal\TransformerAbstract;

class WavePickShowTransformer extends TransformerAbstract
{
    public function transform($wvHdr)
    {
        $details = $wvHdr->wvDtls->transform(function ($wvDtl) {
            $piece_ctn = ceil($wvDtl->piece_qty / data_get($wvDtl, 'item.pack_size'));
            $picked_qty = (int)data_get($wvDtl, 'picked_qty') + (int)data_get($wvDtl, 'over_qty');
            $picked_ctn = ceil($picked_qty / data_get($wvDtl, 'item.pack_size'));
            $totalM3 = data_get($wvDtl, 'item.m3') * $wvDtl->piece_qty;
            $totalLocation = $wvDtl->loadMissing([
                'cartons' => function ($q) use ($wvDtl) {
                    $q->select([
                        'cartons.*',
                        'locations.*'
                    ])
                        ->filterByWvDtl($wvDtl)
                        ->join('locations', function ($loc) {
                            $loc->on('locations.loc_id', '=', 'cartons.loc_id')
                                ->where('locations.deleted', 0);
                        })
                        ->groupBy('locations.loc_id');
                }
            ])->cartons->count();

            return [
                'wv_dtl_id' => $wvDtl->id,
                'cus_id' => $wvDtl->cus_id,
                'picker_id' => $wvDtl->picker_id,
                'picker_name' => data_get($wvDtl, 'picker.name'),
                'wv_dtl_sts' => $wvDtl->wv_dtl_sts,
                'wv_dtl_sts_name' => Config::getStatusName('WV_DTL_STATUS', $wvDtl->wv_dtl_sts),
                'item_code' => data_get($wvDtl, 'item.item_code'),
                'item_name' => data_get($wvDtl, 'item.item_name'),
                'm3' => Helpers::formatNumberTotalM3(data_get($wvDtl, 'item.m3')),
                'total_m3' => Helpers::formatNumberTotalM3($totalM3),
                'sku' => data_get($wvDtl, 'item.sku'),
                'size' => data_get($wvDtl, 'item.size'),
                'color' => data_get($wvDtl, 'item.color'),
                'uom_code' => data_get($wvDtl, 'item.uom.code'),
                'uom_name' => data_get($wvDtl, 'item.uom.name'),
                'pack_size' => data_get($wvDtl, 'item.pack_size'),
                'bin_loc_id' => data_get($wvDtl, 'bin_loc_id'),
                'bin_loc_code' => data_get($wvDtl, 'binLocation.code'),
                'lot' => $wvDtl->lot,
                'piece_qty' => $wvDtl->piece_qty,
                'piece_ctn' => $piece_ctn,
                'picked_qty' => $picked_qty,
                'picked_ctn' => $picked_ctn,
                'put_back_qty' => $wvDtl->put_back_qty,
                'cancelled_qty' => $wvDtl->cancelled_qty,
                'algorithm' => $wvDtl->algorithm ?: data_get($wvDtl, 'customer.algorithm.config_value'),
                'is_serial' => data_get($wvDtl, 'item.serial'),
                'total_locations' => $totalLocation,
                'is_retail' => $wvDtl->lot == Config::RETAIL ? 1 : 0,
            ];
        });

        $orderNums = $wvHdr->order_cancelled
            ? array_merge(explode(',', $wvHdr->order_cancelled), $wvHdr->odrHdrs->pluck('odr_num')->all())
            : $wvHdr->odrHdrs->pluck('odr_num')->all();

        $isOutSorted = $wvHdr->odrHdrs()
            ->whereNotIn('odr_sts', [OrderHdr::STS_CANCELED, OrderHdr::STS_OUT_SORTED])
            ->exists();

        return [
            'wv_hdr_id' => $wvHdr->id,
            'wv_hdr_num' => $wvHdr->wv_hdr_num,
            'odr_nums' => $orderNums ? implode(', ', array_unique($orderNums)) : '',
            'wv_hdr_sts' => $wvHdr->wv_hdr_sts,
            'wv_hdr_sts_name' => Config::getStatusName('WV_HDR_STATUS', $wvHdr->wv_hdr_sts),
            'created_at' => $wvHdr->created_at,
            'updated_at' => $wvHdr->updated_at,
            'created_by_name' => data_get($wvHdr, 'createdBy.name'),
            'updated_by_name' => data_get($wvHdr, 'updatedBy.name'),
            'details' => $details,
            'is_outsorted' => $isOutSorted ? 0 : 1
        ];
    }
}
