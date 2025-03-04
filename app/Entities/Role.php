<?php

namespace App\Entities;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    const ROLE_ADMIN = 'ADMIN';
}
