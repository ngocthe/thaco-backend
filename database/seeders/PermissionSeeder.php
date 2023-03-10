<?php

namespace Database\Seeders;

use App\Constants\Permission as PermissionConstant;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use \App\Constants\Role as RoleConstant;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminGuard = Guard::getDefaultName(Admin::class);
        // Seeding roles
        /** @var Role $adminRole */
        $adminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_ADMIN, 'guard_name' => $adminGuard]);

        foreach (PermissionConstant::getAllPermissions() as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => $adminGuard]);
        }

        $adminRole->syncPermissions(PermissionConstant::getAdminPermissions());


        /** @var Role $peAdminRole */
        $peAdminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_PE_ADMIN, 'guard_name' => $adminGuard]);
        $peAdminRole->syncPermissions(PermissionConstant::getPEAdminPermissions());


        /** @var Role $purAdminRole */
        $purAdminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_PUR_ADMIN, 'guard_name' => $adminGuard]);
        $purAdminRole->syncPermissions(PermissionConstant::getPURAdminPermissions());

        /** @var Role $planAdminRole */
        $planAdminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_PLAN_ADMIN, 'guard_name' => $adminGuard]);
        $planAdminRole->syncPermissions(PermissionConstant::getPlanAdminPermissions());

        /** @var Role $upkAdminRole */
        $upkAdminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_UPK_ADMIN, 'guard_name' => $adminGuard]);
        $upkAdminRole->syncPermissions(PermissionConstant::getUpkAdminPermissions());

        /** @var Role $bwhAdminRole */
        $bwhAdminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_BWH_ADMIN, 'guard_name' => $adminGuard]);
        $bwhAdminRole->syncPermissions(PermissionConstant::getBwhAdminPermissions());

        /** @var Role $plantAdminRole */
        $plantAdminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_PLANT_ADMIN, 'guard_name' => $adminGuard]);
        $plantAdminRole->syncPermissions(PermissionConstant::getPlantAdminPermissions());

        /** @var Role $orhAdminRole */
        $orhAdminRole = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_ORH_ADMIN, 'guard_name' => $adminGuard]);
        $orhAdminRole->syncPermissions(PermissionConstant::getORHAdminPermissions());
    }
}
