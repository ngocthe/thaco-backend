<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Permission;

class PermissionTransformer extends TransformerAbstract
{
    /**
     * @param Permission $permission
     * @return array
     */
    public function transform(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'code' => $permission->name
        ];
    }
}
