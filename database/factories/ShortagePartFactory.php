<?php

namespace Database\Factories;

use App\Constants\MRP;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShortagePartFactory extends Factory
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
            'part_code' => strtoupper(Str::random(10)),
            'part_color_code' => strtoupper(Str::random(2)),
            'quantity' => rand(1, 1000),
            'import_id' => rand(1, 1000),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): ShortagePartFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
