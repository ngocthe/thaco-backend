<?php

namespace Database\Factories;

use App\Models\Bom;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Bom::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'msc_code' => strtoupper(Str::random(10)),
            'shop_code' => strtoupper(Str::random(3)),
            'part_code' => strtoupper(Str::random(10)),
            'part_color_code' => strtoupper(Str::random(2)),
            'quantity' => mt_rand(1, 1000),
            'ecn_in' => strtoupper(Str::random(10)),
            'ecn_out' => strtoupper(Str::random(10)),
            'plant_code' => strtoupper(Str::random(5)),
            'part_remarks' => $this->faker->text(50),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): BomFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
