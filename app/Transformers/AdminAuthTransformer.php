<?php

namespace App\Transformers;

use App\Models\Admin;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class AdminAuthTransformer extends TransformerAbstract
{
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'roles'
    ];

    /**
     * @param Admin $admin
     * @return array
     */
    public function transform(Admin $admin): array
    {
        return [
            'id' => $admin->id,
            'username' => $admin->name,
            'code' => $admin->username,
            'password_default'=>$admin->password_default
        ];
    }

    /**
     * @param Admin $admin
     * @return Collection
     */
    public function includeRoles(Admin $admin): Collection
    {
        $roles = $admin->roles;

        return $this->collection($roles, new RoleTransformer);
    }
}
