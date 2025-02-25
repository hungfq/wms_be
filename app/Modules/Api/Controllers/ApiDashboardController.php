<?php

namespace App\Modules\Api\Controllers;

use App\Http\Controllers\ApiController;

class ApiDashboardController extends ApiController
{
    public function getDashboardTmp()
    {
        return ['data' => []];
    }
}
