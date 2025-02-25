<?php

namespace App\Modules\Inbound\Actions\GR;

use App\Entities\GrHdr;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\Export;
use App\Libraries\Helpers;
use App\Modules\Inbound\DTO\GR\GrViewDTO;
use App\Modules\Inbound\Transformers\GR\GrViewTransformer;
use Illuminate\Support\Facades\DB;

class GrViewAction
{
    public GrViewDTO $dto;

    /**
     * @param GrViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $query = GrHdr::query()
            ->select([
                'gr_hdr.*',
                'customers.name AS cus_name',
                'ctnr.code AS ctnr_name',
                'sts.sts_name AS gr_hdr_sts_name',
                'users.name AS created_by_name',
                'po_hdr.po_num',
                'putter.name AS putter_name',
                'gr_hdr.created_at AS created_dt',
                'po_hdr.expt_date',
                DB::raw('COUNT(DISTINCT gr_dtl.item_id) as of_sku'),
                DB::raw('SUM(gr_dtl.act_ctn_ttl) as act_ctn_ttl'),
                DB::raw('SUM(gr_dtl.act_qty) as act_qty'),
            ])
            ->join('gr_dtl', 'gr_dtl.gr_hdr_id', '=', 'gr_hdr.gr_hdr_id')
            ->join('po_hdr', 'po_hdr.po_hdr_id', '=', 'gr_hdr.po_hdr_id')
            ->join('customers', 'customers.cus_id', '=', 'gr_hdr.cus_id')
            ->join('containers AS ctnr', 'ctnr.ctnr_id', '=', 'gr_hdr.ctnr_id')
            ->join('statuses AS sts', 'sts.sts_code', '=', 'gr_hdr.gr_hdr_sts')
            ->join('users', 'users.id', '=', 'gr_hdr.created_by')
            ->leftJoin('users as putter', 'putter.id', '=', 'gr_hdr.putter_id')
            ->where('sts.sts_type', GrHdr::STATUS_TYPE)
            ->where('gr_dtl.deleted', 0)
            ->groupBy('gr_hdr.gr_hdr_id');

        if ($dto->whs_id) {
            $query->where('gr_hdr.whs_id', $dto->whs_id);
        }

        if ($dto->cus_id) {
            $query->where('gr_hdr.cus_id', $dto->cus_id);
        }

        if ($dto->ctnr_name) {
            $query->where('ctnr.code', 'like', '%' . $dto->ctnr_name . '%');
        }

        if ($dto->gr_hdr_num) {
            $query->where('gr_hdr.gr_hdr_num', 'like', '%' . $dto->gr_hdr_num . '%');
        }

        if ($dto->po_num) {
            $query->where('po_hdr.po_num', 'like', '%' . $dto->po_num . '%');
        }

        if ($dto->ref_code) {
            $query->where('gr_hdr.ref_code', 'like', '%' . $dto->ref_code . '%');
        }

        if ($dto->gr_hdr_sts) {
            $query->where('gr_hdr.gr_hdr_sts', $dto->gr_hdr_sts);
        }

        if ($dto->act_date_from) {
            $query->whereDate('gr_hdr.act_date', '>=', $dto->act_date_from);
        }

        if ($dto->act_date_to) {
            $query->whereDate('gr_hdr.act_date', '<=', $dto->act_date_to);
        }

        if ($dto->expt_date_from) {
            $query->whereDate('po_hdr.expt_date', '>=', $dto->expt_date_from);
        }

        if ($dto->expt_date_to) {
            $query->whereDate('po_hdr.expt_date', '<=', $dto->expt_date_to);
        }

        $query->when($this->dto->po_hdr_id, function ($query, $value) {
            $query->where('po_hdr.po_hdr_id', $value);
        });

        Helpers::sortBuilder($query, $this->dto->toArray(), [
            'ctnr_name' => 'ctnr.code',
            'gr_hdr_sts_name' => 'gr_hdr_sts_name',
            'cus_name' => 'customers.name',
            'created_by_name' => 'users.name',
            'po_num' => 'po_hdr.po_num',
            'expt_date' => 'po_hdr.expt_date',
            'putter_name' => 'putter.name',
            'act_dmg_qty' => 'act_dmg_qty',
        ]);

        if ($exportType = $dto->export_type) {
            return $this->handleDataExport($exportType, $query);
        }

        return $query->paginate(data_get($this->dto, 'limit', ITEM_PER_PAGE));
    }

    public function handleDataExport($exportType, $query)
    {
        $transform = new GrViewTransformer();
        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);

        $orders = $query->limit($limit)->get();

        $data = $orders->transform(function ($order) use ($transform) {
            return $transform->transform($order);
        })->toArray();

        $titles = $transform->getTitleExport();

        return Export::export($exportType, $titles, $data, 'GrList', 'List GR');
    }

    public function isExport(): bool
    {
        if ($this->dto->export_type) {
            return true;
        }

        return false;
    }
}