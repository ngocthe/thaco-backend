<?php

namespace Database\Factories;

use App\Constants\MRP;
use App\Models\BwhOrderRequest;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MrpSimulationResultFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = CarbonImmutable::now();
        return [
            'plan_date' => $now->format('Y-m-d'),
            'msc_code' => Str::random(7),
            'vehicle_color_code' => Str::random(4),
            'production_volume' => rand(1, 10),
            'part_code' => Str::random(10),
            'part_color_code' => Str::random(2),
            'part_requirement_quantity' => rand(10, 200),
            'import_id' => rand(1, 100),
            'plant_code' => Str::random(2),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): MrpOrderCalendarFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
