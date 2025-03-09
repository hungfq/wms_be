<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\BinLocation;
use App\Entities\ContainerType;
use App\Entities\Country;
use App\Entities\Customer;
use App\Entities\Department;
use App\Entities\ItemCategory;
use App\Entities\LocationType;
use App\Entities\OdrType;
use App\Entities\PoType;
use App\Entities\State;
use App\Entities\Uom;
use App\Entities\User;
use App\Entities\Zone;
use App\Http\Controllers\ApiController;
use App\Libraries\Data;
use App\Modules\MasterData\Transformers\Dropdown\BinLocTransformer;
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

    public function binLocs(BinLocTransformer $transformer)
    {
        $data = BinLocation::query()->where('whs_id', Data::getCurWhs())->get();

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

    public function country(BinLocTransformer $transformer)
    {
        $data = Country::query()
            ->where('status', Country::STATUS_ACTIVE)
            ->get();

        return $this->response->collection($data, $transformer);
    }

    public function state(BinLocTransformer $transformer)
    {
        $query = State::query();

        if ($this->request->input('country_id')) {
            $query->where('country_id', $this->request->input('country_id'));
        }

        return $this->response->collection($query->orderBy('priority')->get(), $transformer);
    }

    public function orderTypes(BinLocTransformer $transformer)
    {
        $query = OdrType::query();

        return $this->response->collection($query->get(), $transformer);
    }

    public function department(BinLocTransformer $transformer)
    {
        $query = Department::query();

        return $this->response->collection($query->get(), $transformer);
    }

    public function itemCategory(BinLocTransformer $transformer)
    {
        $query = ItemCategory::query();

        return $this->response->collection($query->get(), $transformer);
    }

    public function uom(BinLocTransformer $transformer)
    {
        $query = Uom::query();

        return $this->response->collection($query->get(), $transformer);
    }

    public function zone(BinLocTransformer $transformer)
    {
        $query = Zone::query()
            ->select([
                'zone_id as id',
                'zone_code as code',
                'zone_name as name',
            ])
            ->where('zone_sts', Zone::DEFAULT_STS_ACTIVE);

        if ($this->request->input('whs_id')) {
            $query->where('whs_id', $this->request->input('whs_id'));
        }

        return $this->response->collection($query->get(), $transformer);
    }

    public function locType(BinLocTransformer $transformer)
    {
        $query = LocationType::query()
            ->select([
                'loc_type_id as id',
                'loc_type_code as code',
                'loc_type_name as name',
            ]);

        return $this->response->collection($query->get(), $transformer);
    }

}
