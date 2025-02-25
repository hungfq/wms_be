<?php

namespace App\Modules\Inbound\Controllers;

use App\Entities\Carton;
use App\Entities\GrDtl;
use App\Entities\GrHdr;
use App\Entities\GrLog;
use App\Entities\Inventory;
use App\Entities\Pallet;
use App\Entities\PoDtl;
use App\Entities\PoHdr;
use App\Entities\Statuses;
use App\Http\Controllers\ApiController;
use App\Libraries\Helpers;
use App\Libraries\Language;
use App\Modules\Inbound\Actions\GR\GrViewAction;
use App\Modules\Inbound\Actions\GR\GrViewLogAction;
use App\Modules\Inbound\DTO\GR\GrViewDTO;
use App\Modules\Inbound\DTO\GR\GRViewLogDTO;
use App\Modules\Inbound\Transformers\GR\GrShowTransformer;
use App\Modules\Inbound\Transformers\GR\GRViewLogTransformer;
use App\Modules\Inbound\Transformers\GR\GrViewTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrController extends ApiController
{
    public function index($whsId, GrViewAction $action, GrViewTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
        ]);

        $result = $action->handle(
            GrViewDTO::fromRequest()
        );

        if ($action->isExport()) {
            return $result;
        }

        return $this->response->paginator($result, $transformer);
    }

    public function show($grHdrId)
    {
        $model = GrHdr::query()->find($grHdrId);

        if (!$model) {
            return $this->responseError(Language::translate('Goods Receipt does not exist'));
        }

        $grDtls = GrDtl::query()
            ->where('gr_hdr_id', $grHdrId)
            ->select([
                'gr_dtl.*',
                'items.item_code',
                'items.item_name',
                'items.sku',
                'items.size',
                'items.color',
                'items.m3',
                'items.pack_size',
                'items.serial',
                'sts.sts_name AS gr_dtl_sts_name',
                'po_dtl.exp_qty',
                'po_dtl.exp_ctn_ttl',
                DB::raw('FORMAT(po_dtl.act_qty * (items.m3 / items.pack_size), 7) as exp_total_m3'),
                DB::raw('FORMAT((gr_dtl.act_qty) * (items.m3 / items.pack_size), 7) as act_total_m3'),
                DB::raw('
                        COALESCE(SUM(
                            CASE
                                WHEN MOD(gr_dtl.act_qty, items.pack_size) = 0
                                    THEN gr_dtl.act_ctn_ttl * items.gross_weight
                                WHEN FLOOR(gr_dtl.act_qty / items.pack_size) > 0 and MOD(gr_dtl.act_qty, items.pack_size) > 0
                                    THEN (FLOOR(gr_dtl.act_qty / items.pack_size) * items.gross_weight) + (MOD(gr_dtl.act_qty, items.pack_size) * items.weight)
                                ELSE gr_dtl.act_qty * items.weight
                            END
                        ), 0) as total_weight
                    ')
            ])
            ->join('po_dtl', function ($q) {
                $q->on('po_dtl.po_dtl_id', '=', 'gr_dtl.po_dtl_id')
                    ->where('po_dtl.deleted', 0);
            })
            ->join('items', 'items.item_id', '=', 'gr_dtl.item_id')
            ->join('statuses AS sts', 'sts.sts_code', '=', 'gr_dtl.gr_dtl_sts')
            ->where('sts.sts_type', 'GR_DTL_STS')
            ->groupBy('gr_dtl.gr_dtl_id')
            ->get();

        $stsName = Statuses::query()
            ->where('sts_type', GrHdr::STATUS_TYPE)
            ->where('sts_code', $model->gr_hdr_sts)
            ->value('sts_name');

//        $pltTtl = Carton::query()->where('gr_hdr_id', $grHdrId)->select(DB::raw("COUNT(DISTINCT plt_id) AS plt_ttl"))->value('plt_ttl');
//        $model->of_pallet = $pltTtl;
        $model->gr_hdr_sts_name = $stsName;

        $model->details = $grDtls->map(function ($detail) {
            $detail->total_weight = Helpers::formatNumberTotalM3($detail->total_weight);
            $detail->exp_total_m3 = Helpers::formatNumberTotalM3($detail->exp_total_m3);
            $detail->act_total_m3 = Helpers::formatNumberTotalM3($detail->act_total_m3);
            $detail->m3 = Helpers::formatNumberTotalM3($detail->m3);
            return $detail;
        })->toArray();

        return $this->response()->item($model, new GrShowTransformer());
    }

    public function complete($whsId, $grHdrId)
    {
        $grHdr = GrHdr::query()->find($grHdrId);

        if (!$grHdr) {
            return $this->responseSuccess(Language::translate('Goods Receipt does not exist'), Response::HTTP_BAD_REQUEST);
        }

        if ($grHdr->gr_hdr_sts == GrHdr::STS_COMPLETE) {
            throw new \Exception(Language::translate('Goods Receipt is already Received'));
        }

        $cartons = Carton::query()
            ->with([
                'poDtl',
                'pallet',
            ])
            ->where('gr_hdr_id', $grHdrId)
            ->select([
                'cartons.ctn_id',
                'cartons.gr_dtl_id',
                'items.m3',
                'items.sku',
                'cartons.po_dtl_id',
                'cartons.item_id',
                'cartons.lot',
                'cartons.loc_id',
                'cartons.loc_code',
                'cartons.loc_name',
                'cartons.cus_id',
                'cartons.plt_id',
                'customers.code AS cus_code',
                'customers.name AS cus_name',
                DB::raw('((cartons.ctn_ttl * cartons.piece_init ) + cartons.piece_remain) AS ttl_qty'),
                DB::raw('ctn_ttl + IF(cartons.piece_remain > 0, 1, 0) as ttl_ctn_qty'),
                'cartons.ctn_ttl',
                'cartons.ctn_sts',
                'cartons.vendor_id',
                'cartons.bin_loc_id',
                'cartons.vendor_id',
                'cartons.manufacture_date',
                'cartons.des',
                'pallet.rfid as plt_rfid',
                DB::raw('cartons.piece_remain * cartons.ctn_ttl * items.m3 as total_m3')
            ])
            ->join('customers', 'customers.cus_id', '=', 'cartons.cus_id')
            ->leftJoin('pallet', 'pallet.plt_id', '=', 'cartons.plt_id')
            ->join('items', 'items.item_id', '=', 'cartons.item_id')
            ->get();

        if (!count($cartons)) {
            return $this->responseError(Language::translate("Goods Receipt is out of inventory"));
        }

        if ($cartons->whereNull('loc_id')->where('ctn_sts', Carton::STS_RECEIVING)->count()) {
            return $this->responseError(Language::translate('Goods Receipt cannot be completed until all pallets/cartons have been put away'));
        }

        $pltIds = Arr::pluck($cartons, 'plt_id', 'plt_id');
        $invts = $grLogData = [];

        foreach ($cartons as $item) {
            $grLogData[] = [
                'gr_hdr_id' => $grHdrId,
                'gr_dtl_id' => $item->gr_dtl_id,
                'po_hdr_id' => data_get($item, 'poDtl.po_hdr_id'),
                'po_dtl_id' => $item->po_dtl_id,
                'loc_id' => $item->loc_id,
                'loc_code' => $item->loc_code,
                'loc_name' => $item->loc_name,
                'plt_id' => $item->plt_id,
                'plt_rfid' => data_get($item, 'pallet.rfid'),
                'plt_num' => data_get($item, 'pallet.plt_num'),
                'plt_is_full' => data_get($item, 'pallet.is_full'),
                'item_id' => $item->item_id,
                'bin_loc_id' => $item->bin_loc_id,
                'lot' => $item->lot,
                'ctn_qty' => $item->ttl_ctn_qty,
                'piece_qty' => $item->ttl_qty,
                'cartons_ids' => \GuzzleHttp\json_encode([$item->ctn_id]),
                'manufacture_date' => $item->manufacture_date,
                'des' => $item->des,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => GrLog::getDefaultDatetimeDeletedAt(),
            ];

            if ($item->ctn_sts != Carton::STS_RECEIVING) {
                continue;
            }

            $itemLot = $whsId . '-' . $item->cus_id . '-' . $item->item_id . '-' . $item->lot . '-' . $item->bin_loc_id;

            if (!isset($invts[$itemLot])) {
                $invts[$itemLot] = [
                    'whs_id' => $whsId,
                    'cus_id' => $item->cus_id,
                    'item_id' => $item->item_id,
                    'bin_loc_id' => $item->bin_loc_id,
                    'lot' => $item->lot,
                    'act_qty' => 0,
                ];
            }

            $invts[$itemLot]['act_qty'] += $item->ttl_qty;
        }

        DB::beginTransaction();

        Pallet::query()
            ->whereIn('plt_id', $pltIds)
            ->where('plt_sts', Pallet::STS_RECEIVING)
            ->update([
                'plt_sts' => Pallet::STS_ACTIVE
            ]);

        Carton::query()
            ->where('gr_hdr_id', $grHdrId)
            ->where('ctn_sts', Carton::STS_RECEIVING)
            ->update([
                'ctn_sts' => Carton::STS_ACTIVE
            ]);

        GrDtl::query()
            ->where('gr_hdr_id', $grHdrId)->update([
                'gr_dtl_sts' => GrDtl::STS_COMPLETE
            ]);

        $grHdr->putaway_cmpl_date = date('Y-m-d');
        $grHdr->gr_hdr_sts = GrHdr::STS_COMPLETE;
        $grHdr->save();

        if (GrHdr::query()->where('po_hdr_id', $grHdr->po_hdr_id)->where('gr_hdr_sts', '<>', GrHdr::STS_COMPLETE)->count() == 0) {
            $sql = "(SELECT COUNT(1) FROM po_dtl
                    WHERE po_dtl.po_hdr_id = po_hdr.po_hdr_id
                        AND po_dtl.exp_qty > (po_dtl.act_qty)
                        AND po_dtl.deleted = 0) = 0";

            PoHdr::query()
                ->where('po_hdr_id', $grHdr->po_hdr_id)
                ->whereRaw($sql)
                ->update([
                    'po_sts' => PoHdr::STS_RECEIVED
                ]);

            PoDtl::query()
                ->where('po_dtl.po_hdr_id', $grHdr->po_hdr_id)
                ->whereRaw('po_dtl.exp_qty <= (po_dtl.act_qty)')
                ->update([
                    'po_dtl_sts' => PoDtl::STS_RECEIVED
                ]);
        }

        foreach ($grHdr->grDtls as $grDtl) {
            $poDtl = $grDtl->poDtl;
            if (!data_get($poDtl, 'received_dt')) {
                $poDtl->update([
                    'received_dt' => date('Y-m-d'),
                ]);
            }
        }

        if (!empty($invts)) {
            foreach ($invts as $invt) {
                $inventory = Inventory::query()
                    ->where([
                        'whs_id' => $whsId,
                        'cus_id' => $invt['cus_id'],
                        'item_id' => $invt['item_id'],
                        'lot' => $invt['lot'],
                        'bin_loc_id' => $invt['bin_loc_id']
                    ])->first();

                if ($inventory) {
                    $inventory->ttl += $invt['act_qty'];
                    $inventory->avail_qty += $invt['act_qty'];
                    $inventory->save();
                } else {
                    Inventory::query()
                        ->create([
                            'whs_id' => $invt['whs_id'],
                            'cus_id' => $invt['cus_id'],
                            'item_id' => $invt['item_id'],
                            'lot' => $invt['lot'],
                            'bin_loc_id' => $invt['bin_loc_id'],
                            'ttl' => $invt['act_qty'],
                            'alloc_qty' => 0,
                            'picked_qty' => 0,
                            'avail_qty' => $invt['act_qty'],
                            'locked_qty' => 0,
                        ]);
                }
            }
        }

        GrLog::query()->insert($grLogData);

        DB::commit();

        return $this->responseSuccess(Language::translate('Completed Goods Receipt Successfully!'));
    }

    public function viewGrLogs($whsId, $grHdrId, GrViewLogAction $action, GRViewLogTransformer $transformer)
    {
        $this->request->merge([
            'whs_id' => $whsId,
            'gr_hdr_id' => $grHdrId,
        ]);

        $results = $action->handle(
            GRViewLogDTO::fromRequest()
        );

        if ($results instanceof Collection) {
            return $this->response->collection($results, $transformer);
        }

        return $this->response->paginator($results, $transformer);
    }
}
