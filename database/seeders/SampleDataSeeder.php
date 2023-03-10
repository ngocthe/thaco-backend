<?php

namespace Database\Seeders;

use App\Models\Bom;
use App\Models\MrpProductionPlanImport;
use App\Models\MrpResult;
use App\Models\MrpWeekDefinition;
use App\Models\ShortagePart;
use App\Models\VehicleColor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mrp_results')->truncate();
        DB::table('shortage_parts')->truncate();
        $importFileIds = MrpProductionPlanImport::query()->select('id')->pluck('id')->toArray();
        $vehicleCodes = VehicleColor::query()->select('code')->pluck('code')->toArray();
        $days = MrpWeekDefinition::query()
            ->select('date')
            ->where('day_off', false)
            ->where('date', '>=', '2022-01-01')
            ->where('date', '<=', '2023-12-31')
            ->pluck('date')->toArray();
        $boms = Bom::query()
            ->select('msc_code', 'part_code', 'part_color_code', 'plant_code')
            ->get()
            ->toArray();
        $mrpResults = [];
        $shortageParts = [];
        $dateStr = Carbon::now()->toDateTimeString();
        foreach ($importFileIds as $importId) {
            $bomsRandom = Arr::random($boms, rand(10, count($boms)));
            foreach ($bomsRandom as $bom) {
                $daysRandom = Arr::random($days, rand(50, count($days) - 50));
                foreach ($daysRandom as $day) {
                    $mrpResults[] = array_merge($bom, [
                        'production_date' => $day,
                        'vehicle_color_code' => Arr::random($vehicleCodes),
                        'production_volume' => rand(1, 10),
                        'part_requirement_quantity' => rand(10, 200),
                        'import_id' => $importId,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_at' => $dateStr,
                        'updated_at' => $dateStr
                    ]);

                    $keys = [$day, $bom['part_code'], $bom['part_color_code'], $bom['plant_code']];
                    $key = implode('-', $keys);
                    if (!isset($shortageParts[$key])) {
                        $shortageParts[$key] = [
                            'plan_date' => $day,
                            'part_code' => $bom['part_code'],
                            'part_color_code'=>  $bom['part_color_code'],
                            'quantity' => rand(-50, 0),
                            'plant_code' =>  $bom['plant_code'],
                            'import_id' => $importId,
                            'created_by' => 1,
                            'updated_by' => 1,
                            'created_at' => $dateStr,
                            'updated_at' => $dateStr
                        ];
                    }
                }
            }
        }

        $mrpResults = array_chunk($mrpResults, 1000);
        foreach ($mrpResults as $rows) {
            MrpResult::query()->insert($rows);
        }

        $shortageParts = array_values($shortageParts);
        $shortageParts = array_chunk($shortageParts, 1000);
        foreach ($shortageParts as $rows) {
            ShortagePart::query()->insert($rows);

        }

    }
}
