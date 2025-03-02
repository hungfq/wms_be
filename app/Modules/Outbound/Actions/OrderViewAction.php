<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\OrderHdr;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\Export;
use App\Libraries\Helpers;
use App\Modules\Outbound\DTO\OrderViewDTO;
use App\Modules\Outbound\Transformers\OrderViewTransformer;
use Illuminate\Support\Facades\DB;

class OrderViewAction
{
    public OrderViewDTO $dto;

    /**
     * @param OrderViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = OrderHdr::query()
            ->with([
                'orderDtls' => function ($q) {
                    $q->select([
                        'odr_dtl.id',
                        'odr_dtl.odr_id',
                        'odr_dtl.price',
                        'items.item_code',
                        'items.item_name',
                        'items.sku as model',
                        'items.m3',
                        'items.pack_size',
                        'items.item_id',

                        'odr_dtl.bin_loc_id',
                        'bin_locations.code as bin_loc_code',
                        'bin_locations.name as bin_loc_name',
                        'odr_dtl.lot as batch',
                        'odr_dtl.piece_qty as total_qty',
                        'odr_dtl.ctn_ttl as total_ctn',
                    ])
                        ->join('items', function ($item) {
                            $item->on('items.item_id', '=', 'odr_dtl.item_id')
                                ->where('items.deleted', 0);
                        })
                        ->leftJoin('bin_locations', function ($binLoc) {
                            $binLoc->on('bin_locations.id', '=', 'odr_dtl.bin_loc_id')
                                ->where('bin_locations.deleted', 0);
                        });
                },
                'department',
                'wvHdr',
                'containerType',
                'odrType',
                'orderDrops',
            ])
            ->select([
                'odr_hdr.*',
                'sts.sts_name AS odr_sts_name',
                'uc.name AS created_by_name',
                'uu.name AS updated_by_name',
                'odr_splits.odr_hdr_id as odr_parent_id',
                DB::raw("(SELECT SUM(odr_dtl.piece_qty) FROM odr_dtl WHERE odr_dtl.odr_id = odr_hdr.id AND deleted = 0) AS total_qty"),
                DB::raw('(SELECT SUM(odr_dtl.ctn_ttl) FROM odr_dtl WHERE odr_dtl.odr_id = odr_hdr.id AND deleted = 0) AS total_ctn'),
                DB::raw("CASE WHEN EXISTS ( SELECT 1 FROM odr_drops WHERE odr_drops.odr_hdr_id = odr_hdr.id AND odr_drops.status = 'NW' AND odr_drops.deleted = '0' ) THEN 1 ELSE 0 END AS is_drop"),
            ])
            ->join('statuses AS sts', 'sts.sts_code', '=', 'odr_hdr.odr_sts')
            ->leftJoin('users AS uc', 'uc.id', '=', 'odr_hdr.created_by')
            ->leftJoin('users AS uu', 'uu.id', '=', 'odr_hdr.updated_by')
            ->leftJoin('wv_hdrs', function ($wvHdr) {
                $wvHdr->on('wv_hdrs.id', '=', 'odr_hdr.wv_id')
                    ->where('wv_hdrs.deleted', 0);
            })
            ->leftJoin('odr_splits', function ($q) {
                $q->on('odr_splits.split_odr_hdr_id', '=', 'odr_hdr.id')
                    ->whereRaw('odr_splits.odr_hdr_id <> odr_hdr.id')
                    ->where('odr_splits.deleted', 0);
            })
            ->where('odr_hdr.whs_id', $this->dto->whs_id)
            ->where('sts.sts_type', OrderHdr::STATUS_TYPE);

        if ($dto->odr_sts ?? null) {
            $statuses = array_unique(array_filter(explode(',', $dto->odr_sts)));

            $query->whereIn('odr_hdr.odr_sts', $statuses);
        }

        if ($dto->odr_num ?? null) {
            $orderNums = array_unique(array_filter(explode(',', $dto->odr_num)));

            $query->where(function ($q) use ($orderNums) {
                foreach ($orderNums as $value) {
                    $q->orWhere('odr_hdr.odr_num', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($this->dto->sku ?? null) {
            $query->whereHas('orderDtls.item', function ($q) {
                $q->where('sku', 'LIKE', "%{$this->dto->sku}%");
            });
        }

        if ($dto->is_drop_orders ?? null) {
            $query->whereHas('orderDrops');
        }

        if ($dto->cus_id ?? null) {
            $query->where('odr_hdr.cus_id', $dto->cus_id);
        }

        if ($dto->ship_to_code ?? null) {
            $query->where('odr_hdr.code', 'LIKE', '%' . $dto->ship_to_code . '%');
        }

        if ($dto->ship_to_name ?? null) {
            $query->where('odr_hdr.ship_to_name', 'LIKE', '%' . $dto->ship_to_name . '%');
        }

        if ($dto->created_at_from ?? null) {
            $query->whereDate('odr_hdr.created_at', '>=', $dto->created_at_from);
        }

        if ($dto->created_at_to ?? null) {
            $query->whereDate('odr_hdr.created_at', '<=', $dto->created_at_to);
        }

        if ($dto->cus_po ?? null) {
            $query->where('odr_hdr.cus_po', 'LIKE', '%' . $dto->cus_po . '%');
        }

        if ($dto->cus_odr_num ?? null) {
            $query->where('odr_hdr.cus_odr_num', 'LIKE', '%' . $dto->cus_odr_num . '%');
        }

        if ($dto->act_shipped_date_from ?? null) {
            $query->whereDate('odr_hdr.shipped_dt', '>=', $dto->act_shipped_date_from);
        }

        if ($dto->act_shipped_date_to ?? null) {
            $query->whereDate('odr_hdr.shipped_dt', '<=', $dto->act_shipped_date_to);
        }

        if ($dto->wv_num ?? null) {
            $wvNums = array_unique(array_filter(explode(',', $dto->wv_num)));

            $query->where(function ($q) use ($wvNums) {
                foreach ($wvNums as $value) {
                    $q->orWhere('wv_hdrs.wv_hdr_num', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        Helpers::sortBuilder($query, $this->dto->toArray(), [
            'odr_sts_name' => 'sts.sts_name',
            'wv_hdr_num' => 'wv_hdrs.wv_hdr_num',
            'odr_type_name' => 'odr_hdr.odr_type',
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',

            'truck_no' => 'odr_hdr.truck_num',
            'driver_name' => 'odr_hdr.driver_info',
            'container_no' => 'odr_hdr.container_num',
            'seal_no' => 'odr_hdr.seal_num',
            'sil_no' => 'odr_hdr.sil_no',
            'bl_no' => 'odr_hdr.bl_no',
            'job_no' => 'odr_hdr.job_no',
            'invoice_no' => 'odr_hdr.invoice_no',
            'zip_no' => 'odr_hdr.zip_no',

            'exp_shipped_date' => 'odr_hdr.ship_by_dt',
            'act_shipped_date' => 'odr_hdr.shipped_dt',

            'ship_to_code' => 'odr_hdr.code',
            'cus_notes' => 'odr_hdr.cus_notes',
            'amount' => 'odr_hdr.amount',
            'carrier' => 'odr_hdr.carrier',
            'voucher' => 'odr_vouchers.voucher',
        ], true);

        $query->orderBy('is_drop', 'desc')
            ->orderBy('odr_hdr.updated_at', 'desc');

        if ($this->dto->export_type) {
            return $this->handleDataExport($query);
        }

        return $query->paginate($dto->limit ?? ITEM_PER_PAGE);
    }

    public function handleDataExport($query)
    {
        $transform = new OrderViewTransformer();
        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);

        $orders = $query->limit($limit)->get();

        $data = $orders->transform(function ($order) use ($transform) {
            return $transform->transform($order);
        })->toArray();

        $titles = $transform->getTitleExport();

        return Export::export($this->dto->export_type, $titles, $data, 'OrderList', 'Order List');
    }

    public function isExport(): bool
    {
        if ($this->dto->export_type) {
            return true;
        }

        return false;
    }
}