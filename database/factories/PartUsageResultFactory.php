<?php

namespace Database\Factories;

use App\Models\PartUsageResult;
use App\Models\PlantInventoryLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PartUsageResultFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = PartUsageResult::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'used_date' => now()->format('Y-m-d'),
            'part_code' => Str::random(10),
            'part_color_code' => Str::random(2),
            'plant_code' => Str::random(5),
            'quantity' => mt_rand(1, 9999),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): PartUsageResultFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
