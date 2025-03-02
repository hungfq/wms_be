<?php

namespace App\Modules\Api\Controllers;

use App\Entities\Location;
use App\Entities\OrderHdr;
use App\Entities\PoHdr;
use App\Entities\WvHdr;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class ApiDashboardController extends ApiController
{
    public function getDashboardTmp()
    {
        return ['data' => []];
    }

    public function getStatisticPo($whsId)
    {
        $fromDate = $this->request->input('from_date');
        $toDate = $this->request->input('to_date');

        $statistic = PoHdr::select([
            DB::raw("SUM(IF(po_sts='NW', 1, 0)) as ttl_po_new"),
            DB::raw("SUM(IF(po_sts='RG', 1, 0)) as ttl_po_receiving"),
            DB::raw("SUM(IF(po_sts='RE', 1, 0)) as ttl_po_received"),
            DB::raw("SUM(IF(po_sts='CC', 1, 0)) as ttl_po_cancel")
        ])
            ->where('whs_id', $whsId);

        if ($fromDate) {
            $statistic->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $statistic->whereDate('created_at', '<=', $toDate);
        }

        $statistic = $statistic->first()->toArray();
        $total = 0;
        foreach ($statistic as $index => $value) {
            $statistic[$index] = (int)$value;
            $total += $value;
        }
        $statistic['ttl_po'] = $total;

        return ['data' => $statistic];
    }

    public function getStatisticOrder($whsId)
    {
        $fromDate = $this->request->input('from_date');
        $toDate = $this->request->input('to_date');

        $statistic = OrderHdr::query()
            ->select([
                DB::raw("SUM(IF(odr_sts='NW', 1, 0)) as ttl_odr_new"),
                DB::raw("SUM(IF(odr_sts='AL', 1, 0)) as ttl_odr_allocated"),
                DB::raw("SUM(IF(odr_sts='PK', 1, 0)) as ttl_odr_picking"),
                DB::raw("SUM(IF(odr_sts='PD', 1, 0)) as ttl_odr_picked"),
                DB::raw("SUM(IF(odr_sts='PN', 1, 0)) as ttl_odr_packing"),
                DB::raw("SUM(IF(odr_sts='PA', 1, 0)) as ttl_odr_packed"),
                DB::raw("SUM(IF(odr_sts='RS', 1, 0)) as ttl_odr_ready_to_ship"),
                DB::raw("SUM(IF(odr_sts='ST', 1, 0)) as ttl_odr_staging"),
                DB::raw("SUM(IF(odr_sts='SH', 1, 0)) as ttl_odr_shipped"),
                DB::raw("SUM(IF(odr_sts='CC', 1, 0)) as ttl_odr_cancel"),
                DB::raw("SUM(IF(odr_sts='OS', 1, 0)) as ttl_odr_out_sorting"),
                DB::raw("SUM(IF(odr_sts='OD', 1, 0)) as ttl_odr_out_sorted"),
                DB::raw("SUM(IF(odr_sts='SS', 1, 0)) as ttl_odr_scheduled_to_ship"),
            ])
            ->where('whs_id', $whsId);

        if ($fromDate) {
            $statistic->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $statistic->whereDate('created_at', '<=', $toDate);
        }

        $statistic = $statistic->first()->toArray();
        $total = 0;
        foreach ($statistic as $index => $value) {
            $statistic[$index] = (int)$value;
            $total += $value;
        }
        $statistic['ttl_odr'] = $total;

        return ['data' => $statistic];
    }

    public function getStatisticWavePick($whsId)
    {
        $fromDate = $this->request->input('from_date');
        $toDate = $this->request->input('to_date');

        $statistic = WvHdr::query()
            ->select([
                DB::raw("SUM(IF(wv_hdr_sts='NW', 1, 0)) as ttl_wv_new"),
                DB::raw("SUM(IF(wv_hdr_sts='PK', 1, 0)) as ttl_wv_picking"),
                DB::raw("SUM(IF(wv_hdr_sts='PD', 1, 0)) as ttl_wv_picked"),
                DB::raw("SUM(IF(wv_hdr_sts='CC', 1, 0)) as ttl_wv_cancel")
            ])
            ->where('whs_id', $whsId);

        if ($fromDate) {
            $statistic->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $statistic->whereDate('created_at', '<=', $toDate);
        }

        $statistic = $statistic->first()->toArray();
        $total = 0;
        foreach ($statistic as $index => $value) {
            $statistic[$index] = (int)$value;
            $total += $value;
        }
        $statistic['ttl_po'] = $total;

        return ['data' => $statistic];
    }

    public function getStatisticLocationCapacity($whsId)
    {
        $fromDate = $this->request->input('from_date');
        $toDate = $this->request->input('to_date');

        $statistic = Location::query()
            ->select([
                DB::raw("COUNT(loc_id) as ttl_loc"),
                DB::raw("SUM(IF(locations.is_full=1, 1, 0)) as ttl_loc_full"),
                DB::raw("SUM(IF(locations.is_full=0 AND EXISTS(SELECT 1 FROM pallet WHERE pallet.loc_id = locations.loc_id AND pallet.deleted = 0), 1, 0)) as ttl_loc_partial"),
                DB::raw("SUM(IF(locations.is_full=0 AND NOT EXISTS(SELECT 1 FROM pallet WHERE pallet.loc_id = locations.loc_id AND pallet.deleted = 0), 1, 0)) as ttl_loc_empty"),
            ])
            ->where('whs_id', $whsId);

        if ($fromDate) {
            $statistic->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $statistic->whereDate('created_at', '<=', $toDate);
        }

        $statistic = $statistic->first()->toArray();

        return ['data' => $statistic];
    }
}
