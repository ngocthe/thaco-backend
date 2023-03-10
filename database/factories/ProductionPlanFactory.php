<?php

namespace Database\Factories;

use App\Models\Plant;
use App\Models\ProductionPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductionPlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = ProductionPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'plan_date' => now()->format('Y-m-d'),
            'msc_code' => Str::random(10),
            'vehicle_color_code' => Str::random(4),
            'volume' => rand(1, 100),
            'import_id' => rand(1, 100),
            'plant_code' => Str::random(5),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): ProductionPlanFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
