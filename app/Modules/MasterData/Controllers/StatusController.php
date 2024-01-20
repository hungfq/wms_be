<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\Statuses;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Str;

class StatusController extends ApiController
{
    public function getAll($sts_type)
    {
        $data = Statuses::query()
            ->select([
                'sts_code',
                'sts_name'
            ])
            ->where('sts_type', Str::upper($sts_type))
            ->orderBy('seq')
            ->get()->toArray();

        return [
            'data' => $data
        ];
    }
}
