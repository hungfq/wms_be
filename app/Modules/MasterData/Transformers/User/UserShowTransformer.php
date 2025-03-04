<?php

namespace App\Modules\MasterData\Transformers\User;

use App\Entities\CustomerInUser;
use App\Entities\Warehouse;
use Illuminate\Support\Arr;
use League\Fractal\TransformerAbstract;

class UserShowTransformer extends TransformerAbstract
{
    public function transform($user)
    {
        $user->customers = CustomerInUser::query()
            ->select([
                'cus_in_user.cus_id',
                'customers.name AS cus_name',
                'customers.code AS cus_code',
                'cus_in_user.whs_id',
                'warehouses.name AS whs_name',
            ])
            ->join('customers', 'customers.cus_id', '=', 'cus_in_user.cus_id')
            ->join('warehouses', 'warehouses.whs_id', '=', 'cus_in_user.whs_id')
            ->where('cus_in_user.user_id', data_get($user, 'id'))
            ->where('customers.deleted', 0)
            ->where('warehouses.deleted', 0)
            ->groupBy('cus_in_user.cus_id', 'cus_in_user.whs_id')
            ->get()->toArray();

        $userWarehouses = $user->warehouses->all();

        $whsIdsOld = Arr::pluck($user->customers, 'whs_id', 'whs_id');
        $whsIdsNew = Arr::pluck($userWarehouses, 'whs_id', 'whs_id');

        $whsIds = array_unique(array_merge($whsIdsOld, $whsIdsNew));
        $user->warehouses = Warehouse::query()
            ->select([
                'warehouses.whs_id',
                'warehouses.name AS whs_name',
                'warehouses.code AS whs_code',
            ])
            ->whereIn('warehouses.whs_id', $whsIds)
            ->where('warehouses.deleted', 0)
            ->get()->toArray();

        return [
            'id' => $user->id,
            'email' => $user->email,
            'status' => $user->status,
            'role' => $user->roles->pluck('name'),
            'role_ids' => $user->roles->pluck('id'),
            'user_name' => $user->name,
            'gender' => data_get($user, 'profile.gender'),
            'first_name' => data_get($user, 'profile.first_name'),
            'last_name' => data_get($user, 'profile.last_name'),
            'full_name' => data_get($user, 'profile.full_name'),
            'image_url' => data_get($user, 'profile.image_url'),
            'contact_email' => data_get($user, 'profile.contact_email'),
            'contact_phone' => data_get($user, 'profile.contact_phone'),
            'code' => data_get($user, 'profile.code'),
            'location' => data_get($user, 'profile.location'),
            'department_id' => data_get($user, 'profile.department_id'),
            'created_at' => $user->created_at,
            'customers' => $user->customers,
            'warehouses' => $user->warehouses,
        ];
    }
}
