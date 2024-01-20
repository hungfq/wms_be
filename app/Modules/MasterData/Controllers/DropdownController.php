<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\ContainerType;
use App\Entities\Customer;
use App\Entities\PoType;
use App\Entities\User;
use App\Http\Controllers\ApiController;
use App\Libraries\Data;
use App\Modules\MasterData\Transformers\Dropdown\ContainerTypeTransformer;
use App\Modules\MasterData\Transformers\Dropdown\CustomerTransformer;
use App\Modules\MasterData\Transformers\Dropdown\PoTypeTransformer;
use App\Modules\MasterData\Transformers\Dropdown\UserByWhsTransformer;

class DropdownController extends ApiController
{
    public function containerTypes(ContainerTypeTransformer $transformer)
    {
        $data = ContainerType::get();

        return $this->response->collection($data, $transformer);
    }

    public function poTypes(PoTypeTransformer $transformer)
    {
        $data = PoType::get();

        return $this->response->collection($data, $transformer);
    }

    public function customers(CustomerTransformer $transformer)
    {
        $data = Customer::query()
            ->where('status', Customer::DEFAULT_STS_ACTIVE)
            ->get();

        return $this->response->collection($data, $transformer);
    }

    public function userByWhs(UserByWhsTransformer $transformer)
    {
        $data = User::query()
            ->where('status', Customer::DEFAULT_STS_ACTIVE)
            ->whereHas('warehouses', function ($q) {
                $q->where('warehouses.whs_id', Data::getCurWhs());
            })
            ->get();

        return $this->response->collection($data, $transformer);
    }
}
