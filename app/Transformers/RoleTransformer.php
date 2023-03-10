<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Role;

class RoleTransformer extends TransformerAbstract
{
    /**
     * @param Role $role
     * @return array
     */
    public function transform(Role $role): array
    {
        return [
            'code' => $role->name,
            'permissions' => $role->permissions->map(function ($permission) { return $permission->name; })
        ];
    }
}
