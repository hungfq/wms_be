<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\OrderHdr;
use App\Libraries\Helpers;
use App\Modules\Outbound\DTO\OrderViewDTO;
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
                        'items.item_code',
                        'items.item_name',
                        'items.sku as model',
                        'items.m3',
                        'items.pack_size',
                        'items.gross_weight',
                        'items.weight',
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
                'splitOrders',
                'wvHdr',
                'containerType',
                'odrType',
                'orderDrops',
            ])
            ->select([
                'odr_hdr.*',
                'odr_vouchers.voucher',
                'sts.sts_name AS odr_sts_name',
                'csr_user.name AS csr_name',
                'uc.name AS created_by_name',
                'uu.name AS updated_by_name',
                'vehicles.number_of_vehicle as number_of_vehicle',
                'vehicles.vehicle_type_id as vehicle_type_id',
                'vehicle_types.name as vehicle_type_name',
                'odr_splits.odr_hdr_id as odr_parent_id',
                DB::raw("(SELECT SUM(odr_dtl.piece_qty) FROM odr_dtl WHERE odr_dtl.odr_id = odr_hdr.id AND deleted = 0) AS total_qty"),
                DB::raw('(SELECT SUM(odr_dtl.ctn_ttl) FROM odr_dtl WHERE odr_dtl.odr_id = odr_hdr.id AND deleted = 0) AS total_ctn'),
                DB::raw("CASE WHEN EXISTS ( SELECT 1 FROM odr_drops WHERE odr_drops.odr_hdr_id = odr_hdr.id AND odr_drops.status = 'NW' AND odr_drops.deleted = '0' ) THEN 1 ELSE 0 END AS is_drop"),
            ])
            ->join('statuses AS sts', 'sts.sts_code', '=', 'odr_hdr.odr_sts')
            ->leftJoin('users AS uc', 'uc.id', '=', 'odr_hdr.created_by')
            ->leftJoin('users AS uu', 'uu.id', '=', 'odr_hdr.updated_by')
            ->leftJoin('users AS csr_user', 'csr_user.id', '=', 'odr_hdr.csr')
            ->leftJoin('odr_vouchers', function ($vch) {
                $vch->on('odr_vouchers.odr_hdr_id', '=', 'odr_hdr.id')
                    ->where('odr_vouchers.deleted', 0);
            })
            ->leftJoin('wv_hdrs', function ($wvHdr) {
                $wvHdr->on('wv_hdrs.id', '=', 'odr_hdr.wv_id')
                    ->where('wv_hdrs.deleted', 0);
            })
            ->leftJoin('odr_splits', function ($q) {
                $q->on('odr_splits.split_odr_hdr_id', '=', 'odr_hdr.id')
                    ->whereRaw('odr_splits.odr_hdr_id <> odr_hdr.id')
                    ->where('odr_splits.deleted', 0);
            })
            ->leftJoin('vehicles', function ($q) {
                $q->on('vehicles.id', '=', 'odr_hdr.vehicle_id')
                    ->where('vehicles.deleted', 0);
            })
            ->leftJoin('vehicle_types', function ($q) {
                $q->on('vehicle_types.id', '=', 'vehicles.vehicle_type_id')
                    ->where('vehicle_types.deleted', 0);
            })
            ->where('odr_hdr.whs_id', $this->dto->whs_id)
            ->where('sts.sts_type', OrderHdr::STATUS_TYPE);

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

        if ($input['odr_type'] ?? null) {
            $query->where('odr_hdr.odr_type', $input['odr_type']);
        }

        if ($input['ship_to_code'] ?? null) {
            $query->where('odr_hdr.code', 'LIKE', '%' . $input['ship_to_code'] . '%');
        }

        if ($input['ship_to_name'] ?? null) {
            $query->where('odr_hdr.ship_to_name', 'LIKE', '%' . $input['ship_to_name'] . '%');
        }

        if ($input['carrier'] ?? null) {
            $query->where('odr_hdr.carrier', 'LIKE', '%' . $input['carrier'] . '%');
        }

        if ($input['voucher'] ?? null) {
            $query->where('odr_vouchers.voucher', 'LIKE', '%' . $input['voucher'] . '%');
        }

        if ($input['created_at_from'] ?? null) {
            $query->whereDate('odr_hdr.created_at', '>=', $input['created_at_from']);
        }

        if ($input['created_at_to'] ?? null) {
            $query->whereDate('odr_hdr.created_at', '<=', $input['created_at_to']);
        }

        if ($input['ship_by_dt_from'] ?? null) {
            $query->whereDate('odr_hdr.ship_by_dt', '>=', $input['ship_by_dt_from']);
        }

        if ($input['ship_by_dt_to'] ?? null) {
            $query->whereDate('odr_hdr.ship_by_dt', '<=', $input['ship_by_dt_to']);
        }

        if ($input['updated_at_from'] ?? null) {
            $query->whereDate('odr_hdr.updated_at', '>=', $input['updated_at_from']);
        }

        if ($input['updated_at_to'] ?? null) {
            $query->whereDate('odr_hdr.updated_at', '<=', $input['updated_at_to']);
        }

        if ($input['carrier'] ?? null) {
            $query->where('odr_hdr.carrier', 'LIKE', '%' . $input['carrier'] . '%');
        }

        if ($input['driver_name'] ?? null) {
            $query->where('odr_hdr.driver_info', 'LIKE', '%' . $input['driver_name'] . '%');
        }

        if ($input['seal_no'] ?? null) {
            $query->where('odr_hdr.seal_num', 'LIKE', '%' . $input['seal_no'] . '%');
        }

        if ($input['sil_no'] ?? null) {
            $query->where('odr_hdr.sil_no', 'LIKE', '%' . $input['sil_no'] . '%');
        }

        if ($input['bl_no'] ?? null) {
            $query->where('odr_hdr.bl_no', 'LIKE', '%' . $input['bl_no'] . '%');
        }

        if ($input['act_shipped_date_from'] ?? null) {
            $query->whereDate('odr_hdr.shipped_dt', '>=', $input['act_shipped_date_from']);
        }

        if ($input['act_shipped_date_to'] ?? null) {
            $query->whereDate('odr_hdr.shipped_dt', '<=', $input['act_shipped_date_to']);
        }

        if ($input['custbody_scv_source_hrv'] ?? null) {
            $query->where('odr_hdr.custbody_scv_source_hrv', 'LIKE', '%' . $input['custbody_scv_source_hrv'] . '%');
        }

        if ($input['custbody_scv_tracking_company'] ?? null) {
            $query->where('odr_hdr.custbody_scv_tracking_company', 'LIKE', '%' . $input['custbody_scv_tracking_company'] . '%');
        }

        if ($input['custbody_scv_tracking_numbers'] ?? null) {
            $query->where('odr_hdr.custbody_scv_tracking_numbers', 'LIKE', '%' . $input['custbody_scv_tracking_numbers'] . '%');
        }

        if ($input['odr_num'] ?? null) {
            $orderNums = array_unique(array_filter(explode(',', data_get($input, 'odr_num'))));

            $query->where(function ($q) use ($orderNums) {
                foreach ($orderNums as $value) {
                    $q->orWhere('odr_hdr.odr_num', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($input['wv_num'] ?? null) {
            $wvNums = array_unique(array_filter(explode(',', data_get($input, 'wv_num'))));

            $query->where(function ($q) use ($wvNums) {
                foreach ($wvNums as $value) {
                    $q->orWhere('wv_hdrs.wv_hdr_num', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($input['invoice_no'] ?? null) {
            $invoiceNos = str_replace(' ', ',', data_get($input, 'invoice_no', ''));
            $invoiceNos = array_unique(array_filter(explode(',', $invoiceNos)));

            $query->where(function ($q) use ($invoiceNos) {
                foreach ($invoiceNos as $value) {
                    $q->orWhere('odr_hdr.invoice_no', '=', trim($value));
                }
            });
        }

        if ($input['cus_po'] ?? null) {
            $doNos = str_replace(' ', ',', data_get($input, 'cus_po', ''));
            $doNos = array_unique(array_filter(explode(',', $doNos)));


            $arrString = explode('%', data_get($input, 'cus_po', ''));
            if ($arrString[0] === '') {
                $query->where(function ($q) use ($doNos) {
                    foreach ($doNos as $value) {
                        $q->orWhere('odr_hdr.cus_po', 'LIKE', "%" . trim($value));
                    }
                });
            } else if ($arrString[count($arrString) - 1] === '') {
                $query->where(function ($q) use ($doNos) {
                    foreach ($doNos as $value) {
                        $q->orWhere('odr_hdr.cus_po', 'LIKE', trim($value) . "%");
                    }
                });
            } else {
                $query->where(function ($q) use ($doNos) {
                    foreach ($doNos as $value) {
                        $q->orWhere('odr_hdr.cus_po', '=', trim($value));
                    }
                });
            }
        }

        if ($input['cus_odr_num'] ?? null) {
            $soNos = array_unique(array_filter(explode(',', data_get($input, 'cus_odr_num'))));

            $query->where(function ($q) use ($soNos) {
                foreach ($soNos as $value) {
                    $q->orWhere('odr_hdr.cus_odr_num', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($input['job_no'] ?? null) {
            $jobNos = array_unique(array_filter(explode(',', data_get($input, 'job_no'))));

            $query->where(function ($q) use ($jobNos) {
                foreach ($jobNos as $value) {
                    $q->orWhere('odr_hdr.job_no', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($input['truck_no'] ?? null) {
            $truckNums = array_unique(array_filter(explode(',', data_get($input, 'truck_no'))));

            $query->where(function ($q) use ($truckNums) {
                foreach ($truckNums as $value) {
                    $q->orWhere('odr_hdr.truck_num', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($input['container_no'] ?? null) {
            $containerNums = array_unique(array_filter(explode(',', data_get($input, 'container_no'))));

            $query->where(function ($q) use ($containerNums) {
                foreach ($containerNums as $value) {
                    $q->orWhere('odr_hdr.container_num', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($input['zip_no'] ?? null) {
            $zipNos = array_unique(array_filter(explode(',', data_get($input, 'zip_no'))));

            $query->where(function ($q) use ($zipNos) {
                foreach ($zipNos as $value) {
                    $q->orWhere('odr_hdr.zip_no', 'LIKE', '%' . trim($value) . '%');
                }
            });
        }

        if ($input['odr_sts'] ?? null) {
            $statuses = array_unique(array_filter(explode(',', data_get($input, 'odr_sts'))));

            $query->whereIn('odr_hdr.odr_sts', $statuses);
        }

        if ($dto->wv_hdr_num ?? null) {
            $query->whereHas('wvHdr', function ($q) {
                $q->where('wv_hdr_num', 'LIKE', '%' . $this->dto->wv_hdr_num . '%');
            });
        }

        if ($input['bin_loc_id'] ?? null) {
            $query->where('odr_dtl.bin_loc_id', '=', $input['bin_loc_id']);
        }

        if ($input['internal_id'] ?? null) {
            $query->where('odr_hdr.internal_id', 'LIKE', '%' . $input['internal_id'] . '%');
        }

        if ($input['sapo_id'] ?? null) {
            $query->where('odr_hdr.sapo_id', 'LIKE', '%' . $input['sapo_id'] . '%');
        }

        if ($input['created_from'] ?? null) {
            $createdFrom = $input['created_from'];
            if ($createdFrom == 'ERP') {
                $query->whereNotNull('odr_hdr.internal_id');
            } else if ($createdFrom == 'SAPO') {
                $query->whereNotNull('odr_hdr.sapo_id');
            }
        }

        if ($input['cus_notes'] ?? null) {
            $query->where('odr_hdr.cus_notes', 'LIKE', '%' . $input['cus_notes'] . '%');
        }

        if ($input['vehicle_id'] ?? null) {
            $query->where('odr_hdr.vehicle_id', $input['vehicle_id']);
        }

        Helpers::sortBuilder($query, $this->dto->toArray(), [
            'odr_sts_name' => 'sts.sts_name',
            'wv_hdr_num' => 'wv_hdrs.wv_hdr_num',
            'csr_name' => 'csr_user.name',
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
            return $query->limit($input['limit'] ?? ITEM_PER_PAGE)->get();
        }

        return $query->paginate($input['limit'] ?? ITEM_PER_PAGE);
    }

    public function isExport(): bool
    {
        if ($this->dto->export_type) {
            return true;
        }

        return false;
    }
}