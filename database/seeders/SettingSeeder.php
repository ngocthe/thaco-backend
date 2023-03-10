<?php

namespace Database\Seeders;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $rows =[
            [
                'key' => 'lock_table_master',
                'value' => json_encode(["end_time" => "21:00", "start_time" => "18:00"]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'key' => 'max_product',
                'value' => "[1000]",
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];
            Setting::query()->insert($rows);
    }
}
