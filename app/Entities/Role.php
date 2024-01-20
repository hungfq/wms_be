<?php

namespace App\Entities;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_LECTURER = 'LECTURER';
    const ROLE_STUDENT = 'STUDENT';
}
