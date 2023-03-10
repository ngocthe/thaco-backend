<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use \App\Constants\Role as RoleConstant;

class DeleteEdPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $edRole = Role::query()->where('name', RoleConstant::ROLE_ED_ADMIN)->first();
        if(isset($edRole)) {
            $edRole->users()->detach();
        }
        $edRole->delete();
    }
}
