<?php

namespace App\Transformers;

use App\Models\Admin;
use App\Traits\IncludeUserTrait;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class AdminTransformer extends TransformerAbstract
{
    use IncludeUserTrait;
    /**
     * @var array|string[]
     */
    protected array $availableIncludes = [
        'remarks', 'user', 'roles'
    ];

    /**
     * @param Admin $admin
     * @return array
     */
    public function transform(Admin $admin): array
    {
        return [
            'id' => $admin->id,
            'code' => $admin->username,
            'username' => $admin->name,
            'name' => $admin->name,
            'company' => $admin->company,
            'created_at' => $admin->created_at->toIso8601String(),
            'updated_at' => $admin->updated_at->toIso8601String(),
            'password_default' => $admin->password_default
        ];
    }

    /**
     * @param Admin $admin
     * @return Collection
     */
    public function includeRoles(Admin $admin): Collection
    {
        $roles = $admin->roles;
        return $this->collection($roles, new RoleWithoutPermissionTransformer);
    }


    /**
     * @param Admin $admin
     * @return Collection
     */
    public function includeRemarks(Admin $admin): Collection
    {

        $remarks = $admin->remarkable;
        return $this->collection($remarks, new RemarkTransformer);
    }
}
