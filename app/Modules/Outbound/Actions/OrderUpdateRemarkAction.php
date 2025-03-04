<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\OrderHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderUpdateRemarkDTO;

class OrderUpdateRemarkAction
{
    public $order;

    /**
     * handle
     *
     * @param OrderUpdateRemarkDTO $dto
     */
    public function handle($dto)
    {
        $this->order = OrderHdr::query()->find($dto->odr_hdr_id);

        if (!$this->order) {
            throw new UserException(Language::translate('Order not found'));
        }

        $this->order->in_notes = data_get($dto, 'internal_remark');
        $this->order->cus_notes = data_get($dto, 'external_remark');
        $this->order->save();
    }
}
