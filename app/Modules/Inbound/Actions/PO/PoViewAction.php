<?php

namespace App\Modules\Inbound\Actions\PO;

use App\Entities\Container;
use App\Entities\Customer;
use App\Entities\PoHdr;
use App\Entities\Vendor;
use App\Entities\WhsConfig;
use App\Libraries\Data;
use App\Libraries\Export;
use App\Libraries\Helpers;
use App\Modules\Inbound\DTO\PO\PoViewDTO;
use App\Modules\Inbound\Transformers\PO\PoViewTransformer;

class PoViewAction
{
    public PoViewDTO $dto;

    /**
     * @param PoViewDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;
        $query = PoHdr::query()
            ->select([
                'po_hdr.*',
                'customers.code as cus_code',
                'customers.name as cus_name',
                'warehouses.code as whs_code',
                'warehouses.name as whs_name',
                'uc.name as created_by_name',
                'uu.name as updated_by_name',
                'po_types.name as po_type_name',
            ])
            ->leftJoin('customers', function ($cus) {
                $cus->on('customers.cus_id', '=', 'po_hdr.cus_id')
                    ->where('customers.deleted', 0);
            })
            ->leftJoin('warehouses', function ($cus) {
                $cus->on('warehouses.whs_id', '=', 'po_hdr.whs_id')
                    ->where('warehouses.deleted', 0);
            })
            ->leftJoin('po_types', function ($cus) {
                $cus->on('po_types.code', '=', 'po_hdr.po_type')
                    ->where('po_types.deleted', 0);
            })
            ->leftJoin('users as uc', 'uc.id', '=', 'po_hdr.created_by')
            ->leftJoin('users as uu', 'uu.id', '=', 'po_hdr.updated_by');

        if ($this->dto->whs_id) {
            $query->where('po_hdr.whs_id', $this->dto->whs_id);
        }

        if ($this->dto->po_sts) {
            $poSts = explode(',', $this->dto->po_sts);
            $query->whereIn('po_hdr.po_sts', $poSts);
        }

        Helpers::sortBuilder($query, $dto->toArray(), [
            'created_by_name' => 'uc.name',
            'updated_by_name' => 'uu.name',
            'cus_name' => Customer::getColumnName('name'),
            'cus_code' => Customer::getColumnName('code'),
            'from_vendor_name' => Vendor::getColumnName('name'),
            'container_code' => Container::getColumnName('code'),
            'sts_name' => 'po_hdr.po_sts',
        ]);

        if ($exportType = $dto->export_type) {
            return $this->handleDataExport($exportType, $query);
        }

        return $query->paginate(data_get($this->dto, 'limit', ITEM_PER_PAGE));
    }

    public function handleDataExport($exportType, $query)
    {
        $transform = new PoViewTransformer();
        $limit = Data::getWhsConfig(WhsConfig::CONFIG_EXPORT_LIMIT);

        $orders = $query->limit($limit)->get();

        $data = $orders->transform(function ($order) use ($transform) {
            return $transform->transform($order);
        })->toArray();

        $titles = $transform->getTitleExport();

        return Export::export($exportType, $titles, $data, 'PoList', 'List PO');
    }

    public function isExport(): bool
    {
        if ($this->dto->export_type) {
            return true;
        }

        return false;
    }
}