<?php

namespace Database\Seeders;

use App\Constants\Role;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SetAllPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $peAdminData = [
                "code" => "pe_admin",
                "name" => "PE Administrator",
                "username" => "pe_admin",
                "email" => "pe_admin@kaopiz.com",
                "password" => Hash::make('Thaco@2022')
            ];
        $peAdmin = Admin::create($peAdminData);
        $peAdmin->assignRole(Role::ROLE_PE_ADMIN);

        $purAdminData = [
            "code" => "pur_admin",
            "name" => "PUR Administrator",
            "username" => "pur_admin",
            "email" => "pur_admin@kaopiz.com",
            "password" => Hash::make('Thaco@2022')
        ];
        $purAdmin = Admin::create($purAdminData);
        $purAdmin->assignRole(Role::ROLE_PUR_ADMIN);

        $planAdminData = [
            "code" => "plan_admin",
            "name" => "Plan Administrator",
            "username" => "plan_admin",
            "email" => "plan_admin@kaopiz.com",
            "password" => Hash::make('Thaco@2022')
        ];
        $planAdmin = Admin::create($planAdminData);
        $planAdmin->assignRole(Role::ROLE_PLAN_ADMIN);

        $bwhAdminData = [
            "code" => "bwh_admin",
            "name" => "BWH Administrator",
            "username" => "bwh_admin",
            "email" => "bwh_admin@kaopiz.com",
            "password" => Hash::make('Thaco@2022')
        ];
        $bwhAdmin = Admin::create($bwhAdminData);
        $bwhAdmin->assignRole(Role::ROLE_BWH_ADMIN);


        $upkAdminData = [
            "code" => "upk_admin",
            "name" => "UPK-WH Administrator",
            "username" => "upk_admin",
            "email" => "upk_admin@kaopiz.com",
            "password" => Hash::make('Thaco@2022')
        ];
        $upkAdmin = Admin::create($upkAdminData);
        $upkAdmin->assignRole(Role::ROLE_UPK_ADMIN);


        $plantAdminData = [
            "code" => "plant_admin",
            "name" => "Plant-WH Administrator",
            "username" => "plant_admin",
            "email" => "plant_admin@kaopiz.com",
            "password" => Hash::make('Thaco@2022')
        ];
        $plantAdmin = Admin::create($plantAdminData);
        $plantAdmin->assignRole(Role::ROLE_PLANT_ADMIN);

        $orhAdminData = [
            "code" => "orh_admin",
            "name" => "ORH-WH Administrator",
            "username" => "orh_admin",
            "email" => "orh_admin@kaopiz.com",
            "password" => Hash::make('Thaco@2022')
        ];
        $orhAdmin = Admin::create($orhAdminData);
        $orhAdmin->assignRole(Role::ROLE_ORH_ADMIN);

    }
}
