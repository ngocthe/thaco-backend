<?php

namespace App\Transformers;

use App\Models\Admin;
use League\Fractal\TransformerAbstract;

class AdminInfoTransformer extends TransformerAbstract
{
    /**
     * @param Admin $admin
     * @return array
     */
    public function transform(Admin $admin): array
    {
        return [
            'id' => $admin->id,
            'code' => $admin->username,
            'username' => $admin->name
        ];
    }
}
