<?php

namespace Database\Factories;

use App\Models\LogicalInventoryPartAdjustment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogicalInventoryPartAdjustmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = LogicalInventoryPartAdjustment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'adjustment_date' => now()->format('Y-m-d'),
            'part_code' => strtoupper(Str::random(10)),
            'part_color_code' => strtoupper(Str::random(2)),
            'old_quantity' => mt_rand(1, 1000),
            'new_quantity' => mt_rand(1, 1000),
            'adjustment_quantity' => mt_rand(1, 1000),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): LogicalInventoryPartAdjustmentFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
