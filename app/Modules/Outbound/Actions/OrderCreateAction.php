<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\ContainerType;
use App\Entities\Department;
use App\Entities\OdrType;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Entities\ThirdParty;
use App\Entities\Warehouse;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderCreateDTO;
use Illuminate\Support\Arr;

class OrderCreateAction
{
    /**
     * @var OrderCreateDTO
     */
    public $dto;
    public $warehouse;
    public $odrType;
    public $department;
    public $orderHdr;
    public $events;

    /**
     * @param OrderCreateDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->makeOrderHdr()
            ->makeOrderDetail()
            ->eventTracking();
    }

    public function checkData()
    {
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

        if ($this->dto->container_type_id) {
            $containerType = ContainerType::query()->find($this->dto->container_type_id);
            if (!$containerType) {
                throw new UserException(Language::translate('Container type not found'));
            }
        }

        $isOrderUnique = OrderHdr::query()
            ->where('whs_id', $this->warehouse->whs_id)
            ->where('cus_odr_num', $this->dto->cus_odr_num)
            ->where('cus_po', $this->dto->cus_po)
            ->where('odr_sts', '!=', OrderHdr::STS_CANCELED)
            ->exists();

        if ($isOrderUnique) {
            throw new UserException(Language::translate('S/O No. and D/O No. already exist'));
        }

        if ($this->dto->odr_type == OrderHdr::TYPE_NISSIN_BACK_ORDER) {
            throw new UserException(Language::translate(
                "Can't create order with order type {0}. Please contact admin for support!",
                OrderHdr::orderType()[$this->dto->odr_type]
            ));
        }

        return $this;
    }

    public function makeOrderHdr()
    {
        $odr_flows = data_get($this->dto, 'odr_flows', []);
        $tpId = $this->createOrUpdateThirdParty(data_get($this->dto, 'cus_id'), $this->dto->toArray());

        $params = [
            'odr_num' => OrderHdr::generateOrderNum(),
            'cus_id' => data_get($this->dto, 'cus_id'),
            'whs_id' => data_get($this->dto, 'whs_id'),
            'cus_odr_num' => data_get($this->dto, 'cus_odr_num'),
            'cus_po' => data_get($this->dto, 'cus_po'),
            'ref_cod' => data_get($this->dto, 'ref_cod'),
            'odr_type' => data_get($this->dto, 'odr_type'),
            'odr_type_id' => data_get($this->dto, 'odr_type_id'),
            'rush_odr' => (int)data_get($this->dto, 'rush_odr'),
            'department_id' => data_get($this->dto, 'department_id'),

            'carrier' => data_get($this->dto, 'carrier'),
            'driver_info' => data_get($this->dto, 'driver_info'),
            'truck_num' => data_get($this->dto, 'truck_num'),
            'container_num' => data_get($this->dto, 'container_num'),
            'container_type_id' => data_get($this->dto, 'container_type_id'),
            'seal_num' => data_get($this->dto, 'seal_num'),
            'tracking_num' => data_get($this->dto, 'tracking_num'),

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
            'cancel_by_dt' => data_get($this->dto, 'cancel_by_dt'),
            'req_cmpl_dt' => data_get($this->dto, 'req_cmpl_dt'),
            'act_cmpl_dt' => data_get($this->dto, 'act_cmpl_dt'),
            'act_cancel_dt' => data_get($this->dto, 'act_cancel_dt'),
            'in_notes' => data_get($this->dto, 'in_notes'),
            'cus_notes' => data_get($this->dto, 'cus_notes'),
            'odr_sts' => OrderHdr::STS_NEW,
            'sku_ttl' => count(Arr::pluck(data_get($this->dto, 'details'), 'item_id', 'item_id')),
            'odr_flows' => \GuzzleHttp\json_encode($odr_flows),
            'transfer_whs_id' => data_get($this->dto, 'transfer_whs_id'),

            'sil_no' => data_get($this->dto, 'sil_no'),
            'bl_no' => data_get($this->dto, 'bl_no'),
            'job_no' => OrderHdr::generateJobNo($this->warehouse, $this->department),
            'invoice_no' => data_get($this->dto, 'invoice_no'),
            'invoice_date' => data_get($this->dto, 'invoice_date'),
            'zip_no' => data_get($this->dto, 'zip_no'),

            'custbody_scv_hrv_num' => data_get($this->dto, 'custbody_scv_hrv_num'),
            'custbody_scv_cus_code_hrv' => data_get($this->dto, 'custbody_scv_cus_code_hrv'),
            'custbody_scv_hrv_cus' => data_get($this->dto, 'custbody_scv_hrv_cus'),
            'custbody_scv_hrv_phone' => data_get($this->dto, 'custbody_scv_hrv_phone'),
            'custbody_scv_hrv_fb' => data_get($this->dto, 'custbody_scv_hrv_fb'),
            'custbody_scv_creator_hrv' => data_get($this->dto, 'custbody_scv_creator_hrv'),
            'custbody_scv_street_hrv' => data_get($this->dto, 'custbody_scv_street_hrv'),
            'custbody_scv_ward_hrv' => data_get($this->dto, 'custbody_scv_ward_hrv'),
            'custbody_scv_district_hrv' => data_get($this->dto, 'custbody_scv_district_hrv'),
            'custbody_scv_province_hrv' => data_get($this->dto, 'custbody_scv_province_hrv'),
            'custbody_scv_country_code_hrv' => data_get($this->dto, 'custbody_scv_country_code_hrv'),
            'custbody_scv_hrv_add' => data_get($this->dto, 'custbody_scv_hrv_add'),
            'custbody_scv_source_hrv' => data_get($this->dto, 'custbody_scv_source_hrv'),
            'custbody_scv_tracking_company' => data_get($this->dto, 'custbody_scv_tracking_company'),
            'custbody_scv_tracking_numbers' => data_get($this->dto, 'custbody_scv_tracking_numbers'),
            'custbody_scv_tax_num_kv' => data_get($this->dto, 'custbody_scv_tax_num_kv'),
            'vehicle_id' => data_get($this->dto, 'vehicle_id'),
        ];

        $this->orderHdr = OrderHdr::query()->create($params);

//        $this->events[] = [
//            'cus_id' => data_get($this->orderHdr, 'cus_id'),
//            'event_code' => EventTracking::ORDER_CREATE,
//            'owner' => data_get($this->orderHdr, 'odr_num'),
//            'transaction' => data_get($this->orderHdr, 'cus_odr_num'),
//            'info' => '{0} created',
//            'info_params' => [data_get($this->orderHdr, 'odr_num')],
//        ];

        return $this;
    }

    public function makeOrderDetail()
    {
        if (!$this->orderHdr) {
            throw new UserException(Language::translate('Please select 1 sku is outbound'));
        }

        $details = data_get($this->dto, 'details');

        foreach ($details as $detail) {
            $this->orderHdr->orderDtls()->create([
                'whs_id' => data_get($this->dto, 'whs_id'),
                'cus_id' => data_get($this->dto, 'cus_id'),
                'odr_id' => $this->orderHdr->id,
                'item_id' => data_get($detail, 'item_id'),
                'bin_loc_id' => data_get($detail, 'bin_loc_id', 1),
                'lot' => data_get($detail, 'lot'),
                'is_retail' => data_get($detail, 'is_retail', 0),
                'ctn_ttl' => (int)data_get($detail, 'ctn_ttl'),
                'piece_qty' => (int)data_get($detail, 'piece_qty'),
                'odr_dtl_sts' => OrderDtl::STS_NEW,
            ]);

//            $this->events[] = [
//                'cus_id' => $this->orderHdr->cus_id,
//                'event_code' => EventTracking::ORDER_CREATE,
//                'owner' => $this->orderHdr->odr_num,
//                'transaction' => $this->orderHdr->cus_odr_num,
//                'info' => '{0} {1} has been created by {2}',
//                'info_params' => [
//                    data_get($detail, 'piece_qty'),
//                    data_get($detail, 'sku'),
//                    $this->orderHdr->odr_num
//                ]
//            ];
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
