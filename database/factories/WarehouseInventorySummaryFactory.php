<?php

namespace Database\Factories;

use App\Models\WarehouseInventorySummary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class WarehouseInventorySummaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WarehouseInventorySummary::class;

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
            'part_code' => strtoupper(Str::random(10)),
            'part_color_code' => strtoupper(Str::random(2)),
            'quantity' => mt_rand(1, 10000),
            'warehouse_type' => Arr::random($warehouseType),
            'warehouse_code' => strtoupper(Str::random(6)),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1,
            'updated_at' => now()
        ];
    }

    public function withDeleted(Carbon $date = null): WarehouseInventorySummaryFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
