<?php

namespace App\Libraries;

use App\Entities\BinLocation;
use App\Entities\Channel;
use App\Entities\ContainerType;
use App\Entities\Country;
use App\Entities\Customer;
use App\Entities\Groups;
use App\Entities\ItemCategory;
use App\Entities\ItemGroup;
use App\Entities\Location;
use App\Entities\OdrDrop;
use App\Entities\OrderHdr;
use App\Entities\Pallet;
use App\Entities\PalletPrefix;
use App\Entities\PalletType;
use App\Entities\Replenishment;
use App\Entities\ReplenishmentDtl;
use App\Entities\ReplenishmentSummary;
use App\Entities\State;
use App\Entities\ThirdPartyWallet;
use App\Entities\User;
use App\Entities\Vendor;
use App\Entities\WhsConfig;
use App\Traits\GetConfigTrait;
use Illuminate\Support\Str;

class Config
{
    use GetConfigTrait;

    const NA = 'NA';
    const ANY = 'ANY';
    const RETAIL = 'RETAIL';

    protected static $configs = [
        'STATUS_TYPE' => [
            'po' => 'po',
            'item' => 'item',
            'SPC_HDL' => 'SPC_HDL',
        ],
        'PO_STATUS' => [
            'NW' => 'New',
            'RG' => 'Receiving',
            'RE' => 'Received',
            'CC' => 'Cancel',
        ],
        'EL_STATUS' => [
            'NW' => 'New',
            'EG' => 'Evaluating',
            'EE' => 'Evaluated',
            'CC' => 'Cancel',
        ],
        'PO_DTL_STATUS' => [
            'NW' => 'New',
            'RG' => 'Receiving',
            'RE' => 'Received',
            'CC' => 'Cancel',
        ],
        'GR_STATUS' => [
            'RG' => 'Receiving',
            'RE' => 'Received',
            'CC' => 'Cancel',
        ],
        'PO_CREATED_FROM' => [
            'GUN' => 'GUN',
            'WMS' => 'WMS'
        ],
        'BLOCK_STOCK_TYPE' => [
            'LC' => 'Location',
            'CU' => 'Customer',
            'IT' => 'Item'
        ],
        'LOCATION_STATUS' => [
            'AC' => 'Active',
            'IA' => 'Inactive',
            'LK' => 'Lock',
        ],
        'CARTON_STATUS' => [
            'AC' => 'Active',
            'IA' => 'Inactive',
            'RG' => 'Receiving',
            'LK' => 'Lock',
            'PD' => 'Picked',
            'SH' => 'Shipped',
        ],
        'ZONE_TYPE' => [
            'RAC' => 'Storage',
            'RCV' => 'Receiving',
            'SHP' => 'Shipping'
        ],
        'BLOCK_STOCK_STATUS' => [
            'LK' => 'Lock',
            'UL' => 'Unlock',
        ],
        'BLOCK_STOCK_DTL_STATUS' => [
            'LK' => 'Lock',
            'UL' => 'Unlock',
        ],
        Customer::STATUS_KEY => [
            Customer::STATUS_ACTIVE => 'Active',
            Customer::STATUS_INACTIVE => 'Inactive',
        ],
        'CUSTOMER_ADDRESS_TYPE' => [
            'BILL' => 'BILL',
            'SHIP' => 'SHIP',
        ],
        'CUSTOMER_PICKING_ALGORITHM' => [
            'FIFO' => [
                'config_name' => 'picking_algorithm',
                'config_value' => 'FIFO',
                'ac' => 'N'
            ],
            'FEFO' => [
                'config_name' => 'picking_algorithm',
                'config_value' => 'FEFO',
                'ac' => 'N'
            ],
            'LIFO' => [
                'config_name' => 'picking_algorithm',
                'config_value' => 'LIFO',
                'ac' => 'N'
            ],
        ],
        Country::STATUS_KEY => [
            Country::STATUS_ACTIVE => 'Active',
            Country::STATUS_INACTIVE => 'Inactive',
        ],
        'LOCATION_TYPE_STATUS' => [
            'AC' => 'Active',
            'IA' => 'Inactive',
        ],
        'ZONE_TYPE_STATUS' => [
            'AC' => 'Active',
            'IA' => 'Inactive',
        ],
        'ITEM_STATUS' => [
            'AC' => 'Active',
            'IA' => 'Inactive',
        ],
        Pallet::STATUS_KEY => [
            Pallet::STS_ACTIVE => 'Active',
            Pallet::STS_RECEIVING => 'Receiving',
            Pallet::STS_PICKED => 'Picked',
            Pallet::STS_SHIPPED => 'Shipped'
        ],
        'ZONE_STATUS' => [
            'AC' => 'Active',
            'IA' => 'Inactive',
        ],
        'REPLENISHMENT_TYPE' => [
            'LS' => 'LOCATION SKU',
            'IT' => 'ITEM',
        ],
        'ORDER_FLOW_TYPE' => [
            'SK' => 'Skip',
            'MN' => 'Manual',
        ],
        'ORDER_FLOW_STATUS' => [
            'NW' => 'New',
            'CP' => 'Completed',
            'ER' => 'Error',
        ],
        'ORDER_DELIVERY_TO' => [
            'NISSIN',
            'CUSTOMER',
            'SECURITY MAN',
            'DRIVER'
        ],
        'ORDER_STATUS' => [
            "AL" => "Allocated",
            "CC" => "Cancelled",
            "NW" => "New",
            "PA" => "Packed",
            "PD" => "Picked",
            "PK" => "Picking",
            "PN" => "Packing",
            "RS" => "Ready To Ship",
            "SH" => "Shipped",
            "ST" => "Staging",
            "SS" => "Scheduled to Ship",
            "OS" => "Out Sorting",
            "OD" => "Out Sorted",
        ],
        'ORDER_DTL_STATUS' => [
            "AL" => "Allocated",
            "CC" => "Cancelled",
            "NW" => "New",
            "PA" => "Packed",
            "PD" => "Picked",
            "PK" => "Picking",
            "PN" => "Packing",
            "RS" => "Ready To Ship",
            "SS" => "Scheduled to Ship",
            "SH" => "Shipped",
            "ST" => "Staging",
            "OS" => "Out Sorting",
            "OD" => "Out Sorted",
        ],
        'PUTBACK_STATUS' => [
            "NW" => "New",
            "IP" => "In-Progress",
            "CP" => "Completed",
        ],
        'PUTBACK_DTL_STATUS' => [
            "NW" => "New",
            "IP" => "In-Progress",
            "CP" => "Completed",
        ],
        'PUTBACK_DTL_ODR_CTN_STATUS' => [
            "NW" => "New",
            "IP" => "In-Progress",
            "CP" => "Completed",
        ],
        'WV_HDR_STATUS' => [
            "NW" => "New",
            "PK" => "Picking",
            "PD" => "Picked",
            "CC" => "Cancelled",
        ],
        'WV_DTL_STATUS' => [
            "NW" => "New",
            "PK" => "Picking",
            "PD" => "Picked",
            "CC" => "Cancelled",
        ],
        'PACK_STATUS' => [
            "AC" => "Active",
            "AS" => "Assigned",
            "CC" => "Cancelled"
        ],
        'RELOCATION_STATUS' => [
            "NW" => "New",
            "CP" => "Completed",
        ],
        'RELOCATION_DTL_STATUS' => [
            "NW" => "New",
            "CP" => "Completed",
        ],
        'RMA_HDR_STATUS' => [
            'NW' => 'New',
            'RG' => 'Receiving',
            'RE' => 'Received'
        ],
        'RMA_DTL_STATUS' => [
            'NW' => 'New',
            'RG' => 'Receiving',
            'RE' => 'Received'
        ],
//        CycleHdr::STATUS_KEY => [
//            CycleHdr::STS_NEW => 'New',
//            CycleHdr::STS_CYCLING => 'Cycling',
//            CycleHdr::STS_CYCLED => 'Cycled',
//            CycleHdr::STS_COMPLETED => 'Completed',
//            CycleHdr::STS_CANCELLED => 'Cancelled',
//        ],
//        CycleDtl::STATUS_KEY => [
//            CycleDtl::STS_NEW => 'New',
//            CycleDtl::STS_CYCLED => 'Cycled',
//        ],
        'LOCATION_STS' => [
            Location::LOCATION_STATUS_ACTIVE => 'Active',
            Location::LOCATION_STATUS_LOCKED => 'Lock'
        ],
        Location::GOODS_TYPE => [
            Location::GOODS_TYPE_RETAIL => 'Retail',
            Location::GOODS_TYPE_WHOLESALE => 'Wholesale'
        ],
        ContainerType::STATUS_KEY => [
            ContainerType::STATUS_ACTIVE => 'Active',
            ContainerType::STATUS_INACTIVE => 'Inactive',
        ],
        Vendor::STATUS_KEY => [
            Vendor::STATUS_ACTIVE => 'Active',
            Vendor::STATUS_INACTIVE => 'Inactive',
        ],
        BinLocation::STATUS_KEY => [
            BinLocation::STATUS_ACTIVE => 'Active',
            BinLocation::STATUS_INACTIVE => 'Inactive',
        ],
        ItemCategory::STATUS_KEY => [
            ItemCategory::STATUS_ACTIVE => 'Active',
            ItemCategory::STATUS_INACTIVE => 'Inactive',
        ],
        State::STATUS_KEY => [
            State::STATUS_ACTIVE => 'Active',
            State::STATUS_INACTIVE => 'Inactive',
        ],
        PalletPrefix::STATUS_KEY => [
            PalletPrefix::STATUS_ACTIVE => 'Active',
            PalletPrefix::STATUS_INACTIVE => 'Inactive',
        ],
        PalletType::STATUS_KEY => [
            PalletType::STATUS_ACTIVE => 'Active',
            PalletType::STATUS_INACTIVE => 'Inactive',
        ],
        Groups::STATUS_KEY => [
            Groups::STATUS_ACTIVE => 'Active',
            Groups::STATUS_INACTIVE => 'Inactive',
        ],
        OrderHdr::COMBINE_KEY => [
            OrderHdr::COMBINE_TYPE_ORIGIN => 'Origin',
            OrderHdr::COMBINE_TYPE_COMBINED => 'Combined',
        ],
        ItemGroup::STATUS_KEY => [
            ItemGroup::STATUS_ACTIVE => 'Active',
            ItemGroup::STATUS_INACTIVE => 'Inactive',
        ],
        Channel::STATUS_KEY => [
            Channel::STATUS_ACTIVE => 'Active',
            Channel::STATUS_INACTIVE => 'Inactive',
        ],
        User::STATUS_KEY => [
            User::STATUS_ACTIVE => 'Active',
            User::STATUS_INACTIVE => 'Inactive',
        ],
//        ThirdPartyGroup::STATUS_KEY => [
//            ThirdPartyGroup::STATUS_ACTIVE => 'Active',
//            ThirdPartyGroup::STATUS_INACTIVE => 'Inactive',
//        ],
        OdrDrop::STATUS_TYPE => [
            OdrDrop::STS_NEW => 'New',
            OdrDrop::STS_COMPLETE => 'Complete',
            OdrDrop::STS_CANCEL => 'Cancel',
        ],
        Replenishment::STATUS_KEY => [
            Replenishment::STATUS_NEW => 'New',
            Replenishment::STATUS_REPLENISHMENT_PICKED => 'Picked',
            Replenishment::STATUS_REPLENISHING => 'Replenishing',
            Replenishment::STATUS_REPLENISHED => 'Replenished',
            Replenishment::STATUS_CANCELED => 'Cancelled',
        ],
        ReplenishmentDtl::STATUS_KEY => [
            ReplenishmentDtl::STATUS_NEW => 'New',
            ReplenishmentDtl::STATUS_PICKED => 'Picked',
            ReplenishmentDtl::STATUS_REPLENISHING => 'Replenishing',
            ReplenishmentDtl::STATUS_REPLENISHED => 'Replenished',
            ReplenishmentDtl::STATUS_CANCELED => 'Cancelled',
        ],
        ReplenishmentSummary::STATUS_KEY => [
            ReplenishmentSummary::STATUS_NEW => 'New',
            ReplenishmentSummary::STATUS_PICKED => 'Picked',
            ReplenishmentSummary::STATUS_REPLENISHING => 'Replenishing',
            ReplenishmentSummary::STATUS_REPLENISHED => 'Replenished',
            ReplenishmentSummary::STATUS_CANCELED => 'Cancelled',
        ],
        ThirdPartyWallet::TYPE_KEY => [
            ThirdPartyWallet::TYPE_ORDER => 'Order',
            ThirdPartyWallet::TYPE_INCREASE_DEBT => 'Increase debt',
            ThirdPartyWallet::TYPE_DECREASE_DEBT => 'Reducing Debt',
        ],
        WhsConfig::CONFIG_KEY => [
            //Common
            WhsConfig::CONFIG_PRINTER => [
                'json_value' => []
            ],
            WhsConfig::CONFIG_PRINTER_FORMATS => [
                'json_value' => []
            ],
            WhsConfig::CONFIG_QRCODE => [
                'json_value' => []
            ],
            WhsConfig::CONFIG_EXPORT_LIMIT => [
                'json_value' => 3000
            ],

            //Inbound
            WhsConfig::CONFIG_PUT_SUG_LOC => [
                'json_value' => 3
            ],
            WhsConfig::CONFIG_ASSIGN_PUTTER => [
                'json_value' => 0
            ],
            WhsConfig::CONFIG_REQUIRE_PUTAWAY_LOCATION => [
                'json_value' => 0
            ],
            WhsConfig::CONFIG_CONFIRM_SCAN_MODEL => [
                'json_value' => 0
            ],

            //Outbound
            WhsConfig::CONFIG_OUTBOUND_PICKING_ITEM_NON_SERIAL_ALLOW_INPUT_QTY => [
                'json_value' => 0
            ],
            WhsConfig::CONFIG_OUTBOUND_PICKING_REQUIRE_PALLET => [
                'json_value' => 0
            ],
            WhsConfig::CONFIG_OUTBOUND_PICKING_CONFIRM_SCAN_MODEL => [
                'json_value' => 0
            ],
            WhsConfig::CONFIG_REQUIRE_SET_PICK_LOCATION => [
                'json_value' => 0
            ],
            WhsConfig::CONFIG_CARTON_DAMAGE_REPORT_NO => [
                'json_value' => 1
            ],
            WhsConfig::CONFIG_CAN_COMBINE_WAVEPICK => [
                'json_value' => 0
            ],
            WhsConfig::CONFIG_PICK_BY_WEB => [
                'json_value' => 1
            ],
            WhsConfig::CONFIG_TIME_OUT_NO_ACTION => [
                'json_value' => 30
            ],
            WhsConfig::CONFIG_REQUIRE_SELECTED_CUS => [
                'json_value' => 1
            ],
            WhsConfig::CONFIG_SHARP_CAPACITY => [
                'json_value' => 10000
            ],
            WhsConfig::CONFIG_HISENSE_CAPACITY => [
                'json_value' => 10000
            ],
            WhsConfig::CONFIG_OUTBOUND_PICKING_REQUIRE_MIN_TIME => [
                'json_value' => 2
            ],
            WhsConfig::CONFIG_DASHBOARD_TV_AUTO_RELOAD => [
                'json_value' => 2
            ],
            WhsConfig::CONFIG_USE_CONTAINER => [
                'json_value' => 0
            ],
        ],

        'PHONE_NUMBER_REGEX' => '/^\+?[0-9 ]+$/',

        'IMAGE_EXPIRE_TIME_IN_SECOND' => 60 * 30,
    ];

    public static function getStatus($key)
    {
        return collect(data_get(self::$configs, $key, []));
    }

    public static function getStatusCode($key, $string)
    {
        $collect = self::getStatus($key);

        $code = $collect->search(function ($item, $key) use ($string) {
            return (Str::upper($item) == Str::upper($string)) || (Str::upper($key) == Str::upper($string));
        });

        if (!$code) {
            return null;
        }

        return $code;
    }

    public static function getStatusName($key, $string)
    {
        $collect = self::getStatus($key);

        $code = $collect->search(function ($item, $key) use ($string) {
            return (Str::upper($item) == Str::upper($string)) || (Str::upper($key) == Str::upper($string));
        });

        if (!$code) {
            return null;
        }

        return $collect->get($code);
    }
}
