<?php

namespace App\Modules\MasterData\Transformers\Role;

use League\Fractal\TransformerAbstract;

class RoleViewTransformer extends TransformerAbstract
{
    public function transform($role)
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'is_edit' => $role->is_edit,
        ];
    }
}
