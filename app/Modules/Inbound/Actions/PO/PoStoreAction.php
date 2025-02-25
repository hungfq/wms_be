<?php

namespace App\Modules\Inbound\Actions\PO;

use App\Entities\PoHdr;
use App\Exceptions\UserException;
use App\Libraries\Config;
use App\Libraries\Data;
use App\Libraries\Language;
use App\Modules\Inbound\DTO\PO\PoStoreDTO;
use Carbon\Carbon;

class PoStoreAction
{
    public PoStoreDTO $dto;

    protected $poHdr;
    protected $poDtls;

    /**
     * handle
     *
     * @param PoStoreDTO $dto
     * @return void
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->addItemLotForParamsInput()
            ->createPoHdr()
            ->createPoDtls()
            ->createEventTracking();
    }

    public function checkData()
    {
        $inputDetails = collect($this->dto->details)->groupBy('item_id_lot_is_delete');
        foreach ($inputDetails as $group) {
            if ($group->count() > 1) {
                $item = $group->first();
                throw new UserException(Language::translate('Duplicate model {0}, please check data submit.', $item->sku));
            }
        }

        return $this;
    }

    public function addItemLotForParamsInput()
    {
        $details = $this->dto->details;

        foreach ($details as &$detail) {
            $detail->item_lot = $detail->item_id . '@' . $detail->lot;
        }
        $this->dto->details = $details;

        return $this;
    }

    public function createPoHdr()
    {
        if (!$this->dto->ref_code) {
            $this->dto->ref_code = Carbon::now()->setTimezone(Data::getConfigTimeZone())->format('ymd');
        }
        $this->dto->po_num = PoHdr::generateNum();
        $this->dto->po_sts = PoHdr::STS_NEW;
        $this->dto->created_from = Config::getByValue('WMS', 'PO_CREATED_FROM');

        $data = $this->dto->except('details')->toArray();

        $this->poHdr = PoHdr::create($data);

        return $this;
    }

    public function createPoDtls()
    {
        foreach ($this->dto->details as $detail) {
            $detail->po_dtl_sts = Config::getByValue('New', 'PO_STATUS');
            $this->poHdr->poDtls()->create($detail->toArray());
        }

        return $this;
    }

    public function createEventTracking()
    {
//        event(new EventTracking([
//            'cus_id' => $this->dto->cus_id,
//            'event_code' => EventTracking::PO_CREATE,
//            'owner' => $this->poHdr->po_num,
//            'transaction' => $this->poHdr->po_num,
//            'info' => 'Create PO #{0} successful',
//            'info_params' => [$this->poHdr->po_num],
//        ]));

        return $this;
    }
}
