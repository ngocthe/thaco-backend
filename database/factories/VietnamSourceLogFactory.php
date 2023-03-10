<?php

namespace Database\Factories;

use App\Models\VietnamSourceLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VietnamSourceLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = VietnamSourceLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'contract_code' => strtoupper(Str::random(5)),
            'invoice_code' => strtoupper(Str::random(5)),
            'bill_of_lading_code' => strtoupper(Str::random(5)),
            'container_code' => strtoupper(Str::random(5)),
            'case_code' => strtoupper(Str::random(5)),
            'part_code' => strtoupper(Str::random(5)),
            'part_color_code' => strtoupper(Str::random(2)),
            'box_type_code' => strtoupper(Str::random(5)),
            'box_quantity' => mt_rand(1, 1000),
            'part_quantity' => mt_rand(1, 1000),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'supplier_code' => strtoupper(Str::random(5)),
            'delivery_date' => now()->format('Y-m-d'),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): VietnamSourceLogFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
