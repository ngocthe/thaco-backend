<?php

namespace Database\Seeders;

use App\Constants\Role;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admins = [
            [
                "code" => "ADMIN",
                "name" => "Admin",
                "username" => "admin",
                "email" => "admin@kaopiz.com",
                "password" => Hash::make('Th@co2022!')
            ],
            [
                "code" => "NhiemBV",
                "name" => "Bùi Văn Nhiêm",
                "username" => "nhiembv",
                "email" => "nhiembv@kaopiz.com",
                "password" => Hash::make('Th@co2022!')
            ],
            [
                "code" => "HieuTD",
                "name" => "Trần Kim Hiếu",
                "username" => "hieutd",
                "email" => "hieutd@kaopiz.com",
                "password" => Hash::make('Th@co2022!')
            ],
            [
                "code" => "HaiND",
                "name" => "Hà Chí Hiếu",
                "username" => "haind",
                "email" => "haind@kaopiz.com",
                "password" => Hash::make('Th@co2022!')
            ],
            [
                "code" => "TheNN",
                "name" => "Ngô Ngọc Thế",
                "username" => "thenn",
                "email" => "thenn@kaopiz.com",
                "password" => Hash::make('Th@co2022!')
            ],
            [
                "code" => "ChiNQ",
                "name" => "Nguyễn Quý Chí",
                "username" => "chinq",
                "email" => "chinq@kaopiz.com",
                "password" => Hash::make('Th@co2022!')
            ],
            [
                "code" => "ToanPV",
                "name" => "Phạm Văn Toàn",
                "username" => "toanpv",
                "email" => "toanpv@kaopiz.com",
                "password" => Hash::make('Th@co2022!')
            ]
        ];
        foreach ($admins as $data) {
            /** @var Admin $admin */
            $admin = Admin::create($data);
            $admin->assignRole(Role::ROLE_ADMIN);
        }
    }
}
