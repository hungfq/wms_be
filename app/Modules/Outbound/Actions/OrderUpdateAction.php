<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\Department;
use App\Entities\OdrType;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Entities\ThirdParty;
use App\Entities\Warehouse;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderUpdateDTO;
use Illuminate\Support\Arr;

class OrderUpdateAction
{
    public OrderUpdateDTO $dto;
    public $warehouse;
    public $odrType;
    public $department;
    public $orderHdr;
    public $events;

    /**
     * @param OrderUpdateDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->updateOrderHdr()
            ->updateOrderDetail()
            ->eventTracking();
    }

    public function checkData()
    {
        $this->orderHdr = OrderHdr::query()->find($this->dto->odr_hdr_id);
        if (!$this->orderHdr) {
            throw new UserException(Language::translate('Order does not exist'));
        }

        $this->warehouse = Warehouse::query()->find($this->dto->whs_id);
        if (!$this->warehouse) {
            throw new UserException(Language::translate('Warehouse not found'));
        }

        $this->odrType = OdrType::query()->find($this->dto->odr_type_id);
        if (!$this->odrType) {
            throw new UserException(Language::translate('Order Type not found'));
        }

        if ($this->dto->department_id) {
            $this->department = Department::query()->find($this->dto->department_id);
            if (!$this->department) {
                throw new UserException(Language::translate('Department not found'));
            }
        }

        if ($this->dto->odr_type == OrderHdr::TYPE_NISSIN_BACK_ORDER) {
            throw new UserException(Language::translate(
                "Can't create order with order type {0}. Please contact admin for support!",
                OrderHdr::orderType()[$this->dto->odr_type]
            ));
        }

        return $this;
    }

    public function updateOrderHdr()
    {
        $tpId = $this->createOrUpdateThirdParty(data_get($this->dto, 'cus_id'), $this->dto->toArray());

        $params = [
            'cus_odr_num' => data_get($this->dto, 'cus_odr_num'),
            'cus_po' => data_get($this->dto, 'cus_po'),
            'odr_type' => data_get($this->dto, 'odr_type'),
            'odr_type_id' => data_get($this->dto, 'odr_type_id'),
            'department_id' => data_get($this->dto, 'department_id'),

            'carrier' => data_get($this->dto, 'carrier'),
            'driver_info' => data_get($this->dto, 'driver_info'),
            'truck_num' => data_get($this->dto, 'truck_num'),
            'seal_num' => data_get($this->dto, 'seal_num'),

            'tp_id' => data_get($this->dto, 'tp_id', $tpId),
            'ship_to_name' => data_get($this->dto, 'ship_to_name'),
            'ship_to_add' => data_get($this->dto, 'ship_to_add'),
            'ship_to_city' => data_get($this->dto, 'ship_to_city'),
            'ship_to_country' => data_get($this->dto, 'ship_to_country'),
            'ship_to_state' => data_get($this->dto, 'ship_to_state'),
            'code' => data_get($this->dto, 'code'),
            'ship_to_zip' => data_get($this->dto, 'zip_code'),
            'ship_to_phone' => data_get($this->dto, 'phone'),
            'ship_to_fax' => data_get($this->dto, 'fax'),
            'vat_code' => data_get($this->dto, 'vat_code'),

            'ship_by_dt' => data_get($this->dto, 'ship_by_dt'),
            'in_notes' => data_get($this->dto, 'in_notes'),
            'cus_notes' => data_get($this->dto, 'cus_notes'),
            'sku_ttl' => count(Arr::pluck(data_get($this->dto, 'details'), 'item_id', 'item_id')),

            'bl_no' => data_get($this->dto, 'bl_no'),
            'invoice_no' => data_get($this->dto, 'invoice_no'),
            'amount' => data_get($this->dto, 'amount'),
        ];

        $this->orderHdr->update($params);

//        $this->events[] = [
//            'cus_id' => data_get($this->orderHdr, 'cus_id'),
//            'event_code' => EventTracking::ORDER_UPDATE,
//            'owner' => data_get($this->orderHdr, 'odr_num'),
//            'transaction' => data_get($this->orderHdr, 'cus_odr_num'),
//            'info' => '{0} updated',
//            'info_params' => [data_get($this->orderHdr, 'odr_num')],
//        ];

        return $this;
    }

    public function updateOrderDetail()
    {
        if ($this->orderHdr->odr_sts == OrderHdr::STS_NEW) {
            $details = data_get($this->dto, 'details');

            $odrDtlIds = [];
            foreach ($details as $detail) {

                if ($dtlId = data_get($detail, 'odr_dtl_id')) {
                    OrderDtl::query()
                        ->where('id', $dtlId)
                        ->update([
                            'item_id' => data_get($detail, 'item_id'),
                            'bin_loc_id' => data_get($detail, 'bin_loc_id', 1),
                            'lot' => data_get($detail, 'lot'),
                            'is_retail' => data_get($detail, 'is_retail', 0),
                            'ctn_ttl' => (int)data_get($detail, 'ctn_ttl'),
                            'piece_qty' => (int)data_get($detail, 'piece_qty'),
                            'price' => (int)data_get($detail, 'price'),
                        ]);

                    $odrDtlIds[] = $dtlId;
                } else {
                    $newDetail = $this->orderHdr->orderDtls()->create([
                        'whs_id' => data_get($this->dto, 'whs_id'),
                        'cus_id' => data_get($this->dto, 'cus_id'),
                        'odr_id' => $this->orderHdr->id,
                        'item_id' => data_get($detail, 'item_id'),
                        'bin_loc_id' => data_get($detail, 'bin_loc_id', 1),
                        'lot' => data_get($detail, 'lot'),
                        'is_retail' => data_get($detail, 'is_retail', 0),
                        'ctn_ttl' => (int)data_get($detail, 'ctn_ttl'),
                        'piece_qty' => (int)data_get($detail, 'piece_qty'),
                        'price' => (int)data_get($detail, 'price'),
                        'odr_dtl_sts' => OrderDtl::STS_NEW,
                    ]);

                    $odrDtlIds[] = $newDetail->id;
                }

//                $this->events[] = [
//                    'cus_id' => $this->orderHdr->cus_id,
//                    'event_code' => EventTracking::ORDER_UPDATE,
//                    'owner' => $this->orderHdr->odr_num,
//                    'transaction' => $this->orderHdr->cus_odr_num,
//                    'info' => '{0} {1} has been updated by {2}',
//                    'info_params' => [
//                        data_get($detail, 'piece_qty'),
//                        data_get($detail, 'sku'),
//                        $this->orderHdr->odr_num
//                    ],
//                ];
            }

            if ($odrDtlIds) {
                OrderDtl::query()
                    ->where('odr_id', data_get($this->orderHdr, 'id'))
                    ->whereNotIn('id', $odrDtlIds)
                    ->delete();
            }
        }

        return $this;
    }

    public function eventTracking()
    {
//        foreach ($this->events as $evt) {
//            event(new EventTracking($evt));
//        }

        return $this;
    }

    public function createOrUpdateThirdParty($cusId, $input)
    {
        $code = data_get($input, 'code');
        $name = data_get($input, 'ship_to_name');
        $add_1 = trim(data_get($input, 'ship_to_add'));
        $city = data_get($input, 'ship_to_city');
        $state = data_get($input, 'ship_to_state');
        $zip = data_get($input, 'zip_code') ?? data_get($input, 'ship_to_zip') ?? null;
        $vat_code = data_get($input, 'vat_code');
        $fax = data_get($input, 'fax');
        $phone = data_get($input, 'phone');

        $thirdParty = ThirdParty::query()
            ->where('cus_id', $cusId)
            ->where('code', $code)
            ->first();

        if (!$thirdParty) {
            $thirdParty = ThirdParty::query()
                ->create([
                    'code' => $code,
                    'name' => $name,
                    'cus_id' => $cusId,
                    'addr_1' => $add_1,
                    'city' => $city,
                    'state_id' => $state,
                    'zip_code' => $zip,
                    'vat_code' => $vat_code,
                    'fax' => $fax,
                    'phone' => $phone,
                ]);
        } else {
            $thirdParty->update([
                'name' => $name,
                'city' => $city,
                'state_id' => $state,
                'zip_code' => $zip,
                'vat_code' => $vat_code,
                'fax' => $fax,
                'phone' => $phone,
            ]);
        }

        return $thirdParty->tp_id;
    }
}
