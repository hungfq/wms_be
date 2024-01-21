<?php

namespace App\Modules\Inbound\Actions\PO;

use App\Entities\PoDtl;
use App\Entities\PoHdr;
use App\Exceptions\UserException;
use App\Libraries\Data;
use App\Libraries\Language;
use App\Modules\Inbound\DTO\PO\PoUpdateDTO;
use Illuminate\Support\Arr;

class PoUpdateAction
{
    public PoUpdateDTO $dto;
    public $user;

    protected $poHdr;
    protected $poDtls;

    /**
     * handle
     *
     * @param PoUpdateDTO $dto
     * @return void
     */
    public function handle($dto)
    {
        $this->dto = $dto;
        $this->user = Data::getCurrentUser();

        $this->checkData()
            ->updatePoHdr()
            ->updatePoDtls()
            ->createEventTracking();
    }

    public function checkData()
    {
        $this->poHdr = PoHdr::where('po_hdr_id', $this->dto->po_hdr_id)
            ->where('whs_id', $this->dto->whs_id)
            ->first();

        if (!$this->poHdr) {
            throw new UserException(Language::translate('PO not found'));
        }

        if (in_array($this->poHdr->po_sts, [PoHdr::STS_CANCEL, PoHdr::STS_RECEIVED])) {
            throw new UserException(Language::translate('PO has been cancel or received, could not update data.'));
        }

        $inputDetails = collect($this->dto->details)->groupBy('item_id_lot_is_delete');
        foreach ($inputDetails as $group) {
            if ($group->count() > 1) {
                $item = $group->first();
                throw new UserException(Language::translate('Duplicate model {0}, please check data submit.', $item->sku));
            }
        }

        $inputPoDtlIds = array_filter(Arr::pluck($this->dto->details, 'po_dtl_id'));

        $this->poDtls = $this->poHdr->poDtls()->whereIn('po_dtl_id', $inputPoDtlIds)->get();

        $diff = collect($inputPoDtlIds)
            ->diff($this->poDtls->pluck('po_dtl_id'));

        if ($diff->count()) {
            throw new UserException(Language::translate('Could not found po_dtl_id(s): {0}', $diff->join(', ')));
        }

        return $this;
    }

    public function updatePoHdr()
    {
        $data = $this->dto->except('details')->toArray();

        $this->poHdr->update($data);

        return $this;
    }

    public function updatePoDtls()
    {
        foreach ($this->dto->details as $detailDTO) {
            if ($detailDTO->po_dtl_id) {
                $poDtl = $this->poDtls->firstWhere('po_dtl_id', $detailDTO->po_dtl_id);

                if (in_array($poDtl->po_dtl_sts, [PoDtl::STS_RECEIVED])) {
                    $poDtl->update(
                        $detailDTO->only('remark')->toArray()
                    );

                    continue;
                }

                if ($detailDTO->is_delete) {
                    $poDtl->delete();
                } else {
                    if ($detailDTO->po_dtl_sts == PoDtl::STS_NEW) {
                        $poDtl->update(
                            $detailDTO->only(
                                'item_id',
                                'lot',
                                'exp_qty',
                                'exp_ctn_ttl',
                                'exp_dt',
                                'remark',
                            )
                                ->toArray()
                        );
                    } elseif ($detailDTO->po_dtl_sts == PoDtl::STS_RECEIVING) {
                        $poDtl->update(
                            $detailDTO->only(
                                'lot',
                                'remark'
                            )->toArray()
                        );
                    }
                }
            } else {
                $data = [
                    'po_dtl_sts' => PoDtl::STS_NEW,
                ];

                $data = array_merge($data, $detailDTO->toArray());

                $this->poHdr->poDtls()->create($data);
            }
        }

        return $this;
    }

    public function createEventTracking()
    {
//        event(new EventTracking([
//            'cus_id' => $this->dto->cus_id,
//            'event_code' => EventTracking::PO_UPDATE,
//            'owner' => $this->poHdr->po_num,
//            'transaction' => $this->poHdr->po_num,
//            'info' => 'Update PO #{0} successful',
//            'info_params' => [$this->poHdr->po_num],
//        ]));

        return $this;
    }
}
