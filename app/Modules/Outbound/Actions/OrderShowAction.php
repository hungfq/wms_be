<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\Inventory;
use App\Entities\OdrCarton;
use App\Entities\OdrSplit;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Entities\State;
use App\Exceptions\UserException;
use App\Libraries\Config;
use App\Libraries\Language;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrderShowAction
{
    public function handle($odrId)
    {
        $orderHdr = OrderHdr::query()->find($odrId);

        if (!$orderHdr) {
            throw new UserException(Language::translate('Order does not exist'));
        }

        $orderDtls = $this->getDetailItemByHdrId($odrId);
        $arrInvt = $skuTotal = $lots = [];

        $itemIds = Arr::pluck($orderDtls, 'item_id', 'item_id');
        $inventories = $this->getAvailableQtyByItem(data_get($orderHdr, 'whs_id'), $itemIds);

        $inventories->groupBy('item_id')->each(function ($invtGroup, $itemId) use (&$lots) {
            $binLocIds = $invtGroup->pluck('bin_loc_id')->unique()->filter();
            foreach ($binLocIds as $binLocId) {
                $lots[$itemId][] = [
                    'lot' => Config::ANY,
                    'bin_loc_id' => $binLocId,
                    'pickable_qty' => 0
                ];
            }

            $anyAvail = [];
            foreach ($invtGroup as $invt) {
                if (!isset($anyAvail[$invt->bin_loc_id])) {
                    $anyAvail[$invt->bin_loc_id] = $invt->avail_qty;
                } else {
                    $anyAvail[$invt->bin_loc_id] += $invt->avail_qty;
                }

                $lots[$itemId][] = [
                    'lot' => $invt->lot,
                    'bin_loc_id' => $invt->bin_loc_id,
                    'pickable_qty' => $invt->avail_qty,
                ];
            }

            foreach ($lots[$itemId] as &$lot) {
                if ($lot['lot'] === 'ANY' && $lot['bin_loc_id']) {
                    $lot['pickable_qty'] = $anyAvail[$lot['bin_loc_id']];
                }
            }
        });


        foreach ($orderDtls as $orderDtl) {
            $skuTotal[$orderDtl->item_id] = $orderDtl->item_id;

            if ($orderHdr->odr_sts == OrderHdr::STS_NEW) {
                $pickableQty = $this->getPickableQty($orderDtl);
                if (!empty($arrInvt[$orderDtl->item_id])) {
                    $orderDtl->pickable_qty = $pickableQty;
                    $orderDtl->lots = array_values($arrInvt[$orderDtl->item_id]);
                } else {
                    $orderDtl->lots = data_get($lots, $orderDtl->item_id) ?? [];
                    $orderDtl->pickable_qty = $pickableQty;
                }
            }

            $orderDtl->alloc_ctns = ceil($orderDtl->alloc_qty / $orderDtl->pack_size);
            $orderDtl->picked_ctns = ceil($orderDtl->picked_qty / $orderDtl->pack_size);
            $orderDtl->packed_ctns = ceil($orderDtl->packed_qty / $orderDtl->pack_size);
            $outSortedQty = OdrCarton::query()
                ->where('odr_dtl_id', '=', $orderDtl->odr_dtl_id)
                ->get()
                ->sum('out_sort_qty');
            $orderDtl->out_sorted_qty = $outSortedQty;
            $orderDtl->out_sorted_ctns = ceil($orderDtl->out_sorted_qty / $orderDtl->pack_size);
        }

        if ($orderHdr->ship_to_state) {
            $state = State::query()->find($orderHdr->ship_to_state);
        }
        $orderHdr->ship_to_state_name = $state ? $state->name : '';
        $orderHdr->ship_to_country_name = $state ? $state->country->name : '';

        $orderHdr->details = $orderDtls->toArray();
        $orderHdr->sku_ttl = count($skuTotal);
        $orderHdr->odr_parent = OdrSplit::query()->where('split_odr_hdr_id', $odrId)->first();
        $orderHdr->is_split_orders = count($orderHdr->splitOrders) ? 1 : 0;
        $orderHdr->is_combine_order = OrderHdr::where('combine_id', $odrId)->count() ? 1 : 0;

        $dropOdrNum = data_get($orderHdr, 'odr_num', '') . '-D';
        $orderHdr->is_drop_order = OrderHdr::where('odr_type', OrderHdr::TYPE_NISSIN_DROP_ORDER)
            ->where('odr_hdr.odr_num', 'LIKE', "%{$dropOdrNum}%")->count() ? 1 : 0;
        $splitOdrIds = $orderHdr->splitOrders->whereNotIn('split_odr_hdr_id', $orderHdr->id)->pluck('split_odr_hdr_id')->toArray();
        $orderHdr->split_odrs = $this->calculateQtySplitOrder($splitOdrIds);

        return $orderHdr;
    }

    protected function getDetailItemByHdrId($odrHdrId)
    {
        return OrderDtl::query()
            ->select([
                'odr_dtl.odr_id',
                'odr_dtl.id AS odr_dtl_id',
                'odr_dtl.whs_id',
                'odr_dtl.cus_id',
                'odr_dtl.item_id',
                'odr_dtl.lot',
                'odr_dtl.is_retail',
                'odr_dtl.ctn_ttl',
                'odr_dtl.piece_qty',
                'odr_dtl.price',
                'odr_dtl.alloc_qty',
                'odr_dtl.picked_qty',
                'odr_dtl.packed_qty',
                'odr_dtl.cancelled_qty',
                'odr_dtl.put_back_qty',
                'odr_dtl.bin_loc_id',
                'items.item_code',
                'items.item_name',
                'items.sku',
                'items.size',
                'items.color',
                'items.pack_size',
                'items.gross_weight',
                'items.weight',
                'items.serial',
                'items.m3',
                'bin_locations.code as bin_loc_code',
                'bin_locations.name as bin_loc_name',
            ])
            ->join('items', 'items.item_id', '=', 'odr_dtl.item_id')
            ->leftJoin('bin_locations', 'bin_locations.id', '=', 'odr_dtl.bin_loc_id')
            ->where('odr_dtl.odr_id', $odrHdrId)
            ->groupBy('odr_dtl.id')
            ->get();
    }

    protected function getAvailableQtyByItem($whsId, $itemIds)
    {
        $query = Inventory::query()
            ->where('inventory.whs_id', $whsId)
            ->whereIn('inventory.item_id', $itemIds)
            ->where('inventory.avail_qty', '>', 0);

        return $query->get();
    }

    protected function getPickableQty($orderDtl)
    {
        if ($orderDtl->lot === Config::ANY) {
            $availQty = Inventory::where('whs_id', $orderDtl->whs_id)
                ->where('cus_id', $orderDtl->cus_id)
                ->where('item_id', $orderDtl->item_id)
                ->where('bin_loc_id', $orderDtl->bin_loc_id)
                ->sum('avail_qty');

            return $availQty;
        } else {
            $result = Inventory::select('avail_qty')
                ->where('whs_id', $orderDtl->whs_id)
                ->where('lot', $orderDtl->lot)
                ->where('cus_id', $orderDtl->cus_id)
                ->where('item_id', $orderDtl->item_id)
                ->where('bin_loc_id', $orderDtl->bin_loc_id)
                ->first();

            return data_get($result, 'avail_qty', 0);
        }
    }

    public function calculateQtySplitOrder($splitOdrIds)
    {
        return OrderHdr::query()->select([
            DB::raw('SUM(piece_qty + cancelled_qty) as ttl_exp_qty'),
            DB::raw('SUM(ctn_ttl) as ttl_exp_ctn'),
            DB::raw('SUM(
                    CASE 
                        WHEN odr_hdr.odr_sts = "NW" THEN 0
                        WHEN odr_hdr.odr_sts = "CC" THEN odr_dtl.cancelled_qty
                        WHEN odr_hdr.odr_sts = "AL" THEN odr_dtl.alloc_qty
                        ELSE odr_dtl.picked_qty
                    END) as total_act_qty'),
            DB::raw('SUM(
                    CASE 
                        WHEN odr_hdr.odr_sts = "NW" THEN 0
                        WHEN odr_hdr.odr_sts = "CC" THEN odr_dtl.cancelled_qty
                        WHEN odr_hdr.odr_sts = "AL" THEN odr_dtl.alloc_qty
                        ELSE odr_dtl.picked_qty
                    END) / items.pack_size as total_act_ctn'),
        ])
            ->join('odr_dtl', function ($dtl) {
                $dtl->on('odr_dtl.odr_id', '=', 'odr_hdr.id')
                    ->where('odr_dtl.deleted', 0);
            })
            ->join('items', function ($dtl) {
                $dtl->on('items.item_id', '=', 'odr_dtl.item_id')
                    ->where('items.deleted', 0);
            })
            ->whereIn('odr_hdr.id', $splitOdrIds)
            ->first();
    }
}