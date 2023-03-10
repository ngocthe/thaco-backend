<?php

namespace Database\Factories;

use App\Models\Procurement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProcurementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Procurement::class;

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
            'minimum_order_quantity' => mt_rand(1, 1000),
            'standard_box_quantity' => mt_rand(1, 1000),
            'part_quantity' => mt_rand(1, 1000),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'supplier_code' => strtoupper(Str::random(5)),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): ProcurementFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
