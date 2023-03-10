<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WarehouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $warehouseType = [
            WarehouseInventorySummary::TYPE_BWH,
            WarehouseInventorySummary::TYPE_UPKWH,
            WarehouseInventorySummary::TYPE_PLANT_WH,
        ];

        return [
            'code' => strtoupper(Str::random(5)),
            'description' => $this->faker->text,
            'warehouse_type' => Arr::random($warehouseType),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): WarehouseFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
