<?php

namespace Database\Factories;

use App\Models\PlantInventoryLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PlantInventoryLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = PlantInventoryLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'part_code' => strtoupper(Str::random(5)),
            'part_color_code' => strtoupper(Str::random(2)),
            'box_type_code' => strtoupper(Str::random(5)),
            'received_date' => now()->format('Y-m-d'),
            'quantity' => mt_rand(1, 1000),
            'received_box_quantity' => mt_rand(1, 5),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'warehouse_code' => strtoupper(Str::random(5)),
            'defect_id' => Arr::random(['W','D','X','S']),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): PlantInventoryLogFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
