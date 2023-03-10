<?php

namespace Database\Seeders;

use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $start = Carbon::now();
        $now = Carbon::now()->format('Y-m-d H:i:s');
        for ($i = 0; $i <= 15000; $i++) {
            $rows = [];
            for ($j = 0; $j <= 10000; $j++) {
                $rows[] = [
                    'code' => 'DEMO',
                    'description' => '',
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            Plant::query()->insert($rows);
        }
        echo Carbon::now()->diffInSeconds($start);
    }
}
