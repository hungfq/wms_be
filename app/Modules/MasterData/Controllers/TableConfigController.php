<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\TableConfig;
use App\Http\Controllers\ApiController;
use App\Libraries\Data;

class TableConfigController extends ApiController
{
    public function index()
    {
        $this->validate($this->request, [
            'table_name' => 'required'
        ]);

        $tableName = $this->request->input('table_name');

        $currentUser = Data::getCurrentUser();

        $tableConfig = TableConfig::select(['user_id', 'table_name', 'value'])
            ->where('user_id', $currentUser->id)
            ->where('table_name', $tableName)
            ->first();

        if ($tableConfig) {
            $tableConfig->value = collect($tableConfig->value)->transform(function ($item) {
                $item['isShow'] = (bool)$item['isShow'];
                return $item;
            });
        }

        return ['data' => $tableConfig];
    }

    public function upsert()
    {
        $attributes = $this->validate($this->request, [
            'table_name' => 'required',
            'user_id' => 'required|integer',
            'value' => 'required'
        ]);

        TableConfig::updateOrCreate(
            ['table_name' => $this->request->input('table_name'), 'user_id' => $this->request->input('user_id')],
            $attributes
        );

        return $this->responseSuccess();
    }
}
