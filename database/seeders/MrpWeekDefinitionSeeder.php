<?php

namespace Database\Seeders;

use App\Models\Plant;
use App\Services\MrpWeekDefinitionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MrpWeekDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mrp_week_definitions')->truncate();
        $service = new MrpWeekDefinitionService();
        $firstDay = Carbon::create(2020);
        $dateString = $firstDay->toDateString();
        $rows = [];
        $year = $firstDay->year;
        while ($year <= 2120) {
            list($monthNo, $weekNo, $yearNo) = $service->getMonthWeekNoOfDate($firstDay->toDateString());
            $rows[] = [
                'date' => $dateString,
                'day_off' => $firstDay->dayOfWeekIso == 7,
                'year' => $yearNo,
                'month_no' => $monthNo,
                'week_no' => $weekNo,
                'created_by' => 1,
                'updated_by' => 1
            ];
            $dateString = $firstDay->addDay()->toDateString();
            $year = $firstDay->year;
        }
        $rows = array_chunk($rows, 365);
        foreach ($rows as $row) {
            DB::table('mrp_week_definitions')->insert($row);
            var_dump("Insert date: " . $row[0]['date']);
        }

    }
}
