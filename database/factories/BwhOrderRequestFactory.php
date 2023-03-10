<?php

namespace Database\Factories;

use App\Models\BwhOrderRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BwhOrderRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = BwhOrderRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_number' => $this->faker->text(100),
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
            'warehouse_code' => strtoupper(Str::random(5)),
            'warehouse_location_code' => strtoupper(Str::random(5)),
            'plant_code' => strtoupper(Str::random(5)),
            'status' => mt_rand(1, 10),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): BwhOrderRequestFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
