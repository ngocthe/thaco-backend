<?php

namespace Database\Factories;

use App\Models\UpkwhInventoryLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UpkwhInventoryLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = UpkwhInventoryLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = now()->format('Y-m-d');
        return [
            'contract_code' => Str::random(5),
            'invoice_code' => Str::random(5),
            'bill_of_lading_code' => Str::random(5),
            'container_code' => Str::random(5),
            'case_code' => Str::random(5),
            'part_code' => strtoupper(Str::random(5)),
            'part_color_code' => strtoupper(Str::random(2)),
            'box_type_code' => strtoupper(Str::random(5)),
            'box_quantity' => mt_rand(1, 100),
            'part_quantity' => mt_rand(1, 100),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'supplier_code' => strtoupper(Str::random(5)),
            'received_date' => $now,
            'warehouse_location_code' => strtoupper(Str::random(5)),
            'warehouse_code' => strtoupper(Str::random(5)),
            'shipped_box_quantity' => mt_rand(1, 100),
            'shipped_date' => $now,
            'defect_id' => Arr::random(['W','D','X','S']),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): UpkwhInventoryLogFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
