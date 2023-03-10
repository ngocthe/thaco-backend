<?php

namespace Database\Seeders;

use App\Constants\Role as RoleConstant;
use App\Constants\RoleBulkWHControl;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role;

class RoleBWHControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminGuard = Guard::getDefaultName(Admin::class);
        $now = Carbon::now();
        $roles = array_map(function($role) use ($adminGuard, $now) {
            return [
                'name' => $role,
                'guard_name' => $adminGuard,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }, RoleBulkWHControl::allRoleBWHControl());
        Role::query()->insert($roles);
    }
}
