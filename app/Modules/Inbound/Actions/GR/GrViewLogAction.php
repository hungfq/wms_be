<?php

namespace App\Modules\Inbound\Actions\GR;

use App\Entities\Carton;
use App\Entities\GrHdr;
use App\Entities\GrLog;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Inbound\DTO\GR\GRViewLogDTO;
use Illuminate\Support\Facades\DB;

class GrViewLogAction
{
    public $grHdr;

    /**
     * @param GRViewLogDTO $dto
     */
    public function handle($dto)
    {
        $this->grHdr = GrHdr::query()->find($dto->gr_hdr_id);
        if (!$this->grHdr) {
            throw new UserException(Language::translate('Goods Receipt not found'));
        }

        if ($this->grHdr->gr_hdr_sts == GrHdr::STS_COMPLETE) {
            $query = GrLog::query()
                ->select([
                    'gr_logs.*',
                    'items.sku',
                    'items.item_name',
                    'items.pack_size',
                    'bin_locations.code as bin_loc_code',
                    'bin_locations.name as bin_loc_name',
                    DB::raw('SUM(gr_logs.ctn_qty) as ttl_ctn_qty'),
                    DB::raw('SUM(gr_logs.piece_qty) as ttl_piece_qty'),
                ])
                ->join('items', function ($q) {
                    $q->on('items.item_id', '=', 'gr_logs.item_id');
                })
                ->leftJoin('bin_locations', function ($q) {
                    $q->on('bin_locations.id', '=', 'gr_logs.bin_loc_id')
                        ->where('bin_locations.deleted', 0);
                })
                ->groupBy(
                    'gr_logs.gr_hdr_id',
                    'gr_logs.gr_dtl_id',
                    'gr_logs.loc_id',
                    'gr_logs.plt_id',
                    'gr_logs.item_id',
                    'gr_logs.bin_loc_id',
                    'gr_logs.lot',
                    'gr_logs.manufacture_date',
                )
                ->where('gr_hdr_id', $dto->gr_hdr_id);

            if ($search = $dto->search) {
                $query->where(function ($q) use ($search) {
                    $q->where('plt_rfid', 'LIKE', "%$search%")
                        ->orWhere('loc_code', 'LIKE', "%$search%")
                        ->orWhere('items.sku', 'LIKE', "%$search%");
                });
            }
        } else {
            $query = Carton::query()
                ->select([
                    'cartons.*',
                    'items.sku',
                    'items.item_name',
                    'items.pack_size',
                    'pallet.rfid as plt_rfid',
                    'pallet.is_full as plt_is_full',
                    'bin_locations.code as bin_loc_code',
                    'bin_locations.name as bin_loc_name',
                    DB::raw('ctn_ttl + IF(cartons.piece_remain > 0, 1, 0) as ttl_ctn_qty'),
                    DB::raw('((cartons.ctn_ttl * cartons.piece_init ) + cartons.piece_remain) as ttl_piece_qty'),
                    DB::raw('CASE WHEN cartons.loc_code IS NULL THEN 0 ELSE 1 END as priority'),
                ])
                ->join('items', function ($q) {
                    $q->on('items.item_id', '=', 'cartons.item_id');
                })
                ->leftJoin('bin_locations', function ($q) {
                    $q->on('bin_locations.id', '=', 'cartons.bin_loc_id')
                        ->where('bin_locations.deleted', 0);
                })
                ->leftJoin('pallet', function ($plt) {
                    $plt->on('pallet.plt_id', '=', 'cartons.plt_id')
                        ->where('pallet.deleted', 0);
                })
                ->groupBy(
                    'cartons.ctn_id',
                )
                ->where('gr_hdr_id', $dto->gr_hdr_id)
                ->orderBy('priority');

            if ($search = $dto->search) {
                $query->where(function ($q) use ($search) {
                    $q->where('pallet.rfid', 'LIKE', "%$search%")
                        ->orWhere('cartons.loc_code', 'LIKE', "%$search%")
                        ->orWhere('items.sku', 'LIKE', "%$search%");
                });
            }
        }

        if ($dto->limit) {
            return $query->paginate($dto->limit);
        }

        return $query->get();
    }
}
