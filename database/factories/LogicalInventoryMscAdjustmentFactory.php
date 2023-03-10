<?php

namespace Database\Factories;

use App\Models\LogicalInventoryMscAdjustment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogicalInventoryMscAdjustmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = LogicalInventoryMscAdjustment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'msc_code' => strtoupper(Str::random(7)),
            'adjustment_quantity' => mt_rand(1, 1000),
            'vehicle_color_code' => strtoupper(Str::random(4)),
            'production_date' => now()->format('Y-m-d'),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): LogicalInventoryMscAdjustmentFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
