<?php

namespace Database\Seeders;

use App\Constants\Role as RoleConstant;
use App\Constants\RoleBulkWHControl;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role;

class AdminBWHControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::create([
            "code" => "administrator",
            "name" => "Administrator",
            "username" => "administrator",
            "password" => Hash::make('Th@co2022!')
        ]);
        $admin->assignRole(RoleBulkWHControl::ROLE_ADMIN_BULK);

    }
}
