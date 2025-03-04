<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\WvHdr;
use App\Libraries\Helpers;

class WavePickViewAction
{
    public function search($request, $isPaginate = true, $export = false)
    {
        $query = WvHdr::query()
            ->with([
                'wvDtls',
                'pickers',
            ])
            ->select([
                WvHdr::query()->qualifyColumn('*'),
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
            ]);

        if ($whsId = $request->input('whs_id')) {
            $query->where('whs_id', $whsId);
        }

        if ($wvHdrNum = $request->input('wv_hdr_num')) {
            $query->where(WvHdr::query()->qualifyColumn('wv_hdr_num'), 'LIKE', "%{$wvHdrNum}%");
        }

        if ($wvHdrSts = $request->input('wv_hdr_sts')) {
            $query->where(WvHdr::query()->qualifyColumn('wv_hdr_sts'), $wvHdrSts);
        }

        if ($createdFrom = $request->input('created_at_from')) {
            $query->whereDate(WvHdr::query()->qualifyColumn('created_at'), '>=', $createdFrom);
        }

        if ($createdTo = $request->input('created_at_to')) {
            $query->whereDate(WvHdr::query()->qualifyColumn('created_at'), '<=', $createdTo);
        }

        if ($odrNum = $request->input('odr_num')) {
            $query->whereHas('odrHdrs', function ($q) use ($odrNum) {
                $q->where('odr_num', 'LIKE', "%{$odrNum}%");
            });
        }

        if ($blNo = $request->input('bl_no')) {
            $query->whereHas('odrHdrs', function ($q) use ($blNo) {
                $q->where('bl_no', 'LIKE', "%$blNo%");
            });
        }

        if ($request->input('truck_num')) {
            $query->whereHas('odrHdrs', function ($q1) use ($request) {
                $truckNums = array_unique(array_filter(explode(',', $request->input('truck_num'))));

                $q1->where(function ($q2) use ($truckNums) {
                    foreach ($truckNums as $value) {
                        $q2->orWhere('truck_num', 'LIKE', '%' . trim($value) . '%');
                    }
                });
            });
        }

        if ($createdByName = $request->input('created_by_name')) {
            $query->where('uc.name', 'LIKE', "%$createdByName%");
        }

        if ($createdById = $request->input('created_by_id')) {
            $query->where('uc.id', $createdById);
        }

        if ($sku = $request->input('sku')) {
            $query->whereHas('wvDtls.item', function ($q) use ($sku) {
                $q->where('sku', 'LIKE', "%$sku%");
            });
        }

        if ($binLocId = $request->input('bin_loc_id')) {
            $query->whereHas('wvDtls', function ($q) use ($binLocId) {
                $q->where('bin_loc_id', $binLocId);
            });
        }

        $query->leftJoin('users as uc', 'uc.id', '=', WvHdr::query()->qualifyColumn('created_by'))
            ->leftJoin('users as uu', 'uu.id', '=', WvHdr::query()->qualifyColumn('updated_by'));

        Helpers::sortBuilder($query, $request->all(), [
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',
            'wv_hdr_sts_name' => WvHdr::query()->qualifyColumn('wv_hdr_sts')
        ]);

        if ($isPaginate) {
            return $query->paginate($request->input('limit', 20));
        }

        if ($export) {
            return $query->limit($request->input('limit') ?? ITEM_PER_PAGE)->get();
        }

        return $query->get();
    }
}