<?php

namespace Database\Factories;

use App\Models\WarehouseSummaryAdjustment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WarehouseSummaryAdjustmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = WarehouseSummaryAdjustment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'warehouse_code' => strtoupper(Str::random(5)),
            'part_code' => strtoupper(Str::random(5)),
            'part_color_code' => strtoupper(Str::random(2)),
            'old_quantity' => mt_rand(1, 1000),
            'new_quantity' => mt_rand(1, 1000),
            'adjustment_quantity' => mt_rand(1, 1000),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): WarehouseSummaryAdjustmentFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
