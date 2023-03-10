<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Role;

class RoleWithoutPermissionTransformer extends TransformerAbstract
{
    /**
     * @param Role $role
     * @return array
     */
    public function transform(Role $role): array
    {
        return [
            'code' => $role->name,
            'name' => $role->display_name,
        ];
    }
}
