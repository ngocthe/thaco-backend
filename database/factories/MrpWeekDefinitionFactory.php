<?php

namespace Database\Factories;

use App\Services\MrpWeekDefinitionService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

class MrpWeekDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = CarbonImmutable::now();
        $date = $now->format('Y-m-d');
        list($monthNo, $weekNo, $year) = app(MrpWeekDefinitionService::class)->getMonthWeekNoOfDate($date);
        return [
            'date' => $date,
            'day_off' => rand(true , false),
            'year' => (int)$year,
            'month_no' => (int)$monthNo,
            'week_no' => (int)$weekNo,
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): MrpWeekDefinitionFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
