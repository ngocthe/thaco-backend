<?php

namespace Database\Factories;

use App\Models\VehicleColor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VehicleColorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = VehicleColor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => strtoupper(Str::random(4)),
            'type' => strtoupper(Str::random(3)),
            'name' => $this->faker->colorName,
            'ecn_in' => strtoupper(Str::random(10)),
            'ecn_out' => strtoupper(Str::random(10)),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): VehicleColorFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
