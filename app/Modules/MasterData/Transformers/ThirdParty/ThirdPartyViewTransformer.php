<?php

namespace App\Modules\MasterData\Transformers\ThirdParty;

use League\Fractal\TransformerAbstract;

class ThirdPartyViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'tp_id' => data_get($model, 'tp_id'),
            'cus_id' => data_get($model, 'cus_id'),
            'cus_name' => data_get($model, 'cus_name'),
            'code' => data_get($model, 'code'),
            'name' => data_get($model, 'name'),
            'des' => data_get($model, 'des'),
            'debt_amount' => data_get($model, 'debt_amount'),
            'mobile' => data_get($model, 'mobile'),
            'phone' => data_get($model, 'phone'),
            'email' => data_get($model, 'email'),
            'zip_code' => data_get($model, 'zip_code'),
            'fax' => data_get($model, 'fax'),
            'state_id' => data_get($model, 'state_id'),
            'state_name' => data_get($model, 'state_name'),
            'area_id' => data_get($model, 'area_id'),
            'area_name' => data_get($model, 'area_name'),
            'country_id' => data_get($model, 'country_id'),
            'country_name' => data_get($model, 'country_name'),
            'address' => data_get($model, 'addr_1'),
            'location' => data_get($model, 'location'),
            'city' => data_get($model, 'city'),
            'vat_code' => data_get($model, 'vat_code'),
            'created_date' => data_get($model, 'created_at'),
            'created_by' => data_get($model, 'created_by'),
            'created_by_name' => data_get($model, 'created_by_name'),
            'updated_date' => data_get($model, 'updated_at'),
            'updated_by' => data_get($model, 'updated_by'),
            'updated_by_name' => data_get($model, 'updated_by_name'),
        ];
    }

    public function getTitleExport()
    {
        return [
            'code' => 'Code',
            'name' => 'Name',
            'phone' => 'Phone',
            'address' => 'Address',
            'debt_amount' => 'Debt Amount',
            'vat_code' => 'VAT Code',
            'des' => 'Description',
            'created_date|format_datetime' => 'Created Date',
            'created_by_name' => 'Created By',
            'updated_date|format_datetime' => 'Updated Date',
            'updated_by_name' => 'Created By',
        ];
    }
}
