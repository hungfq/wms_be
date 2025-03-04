<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\Role;
use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Transformers\Role\RoleViewTransformer;
use Illuminate\Http\Request;

class RoleController extends ApiController
{
    public function view(Request $request)
    {
        $roles = Role::query()->with('permissions')->get();

        return $this->response->collection($roles, new RoleViewTransformer);
    }
}
