<?php

namespace Database\Factories;

use App\Models\PartColor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PartColorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = PartColor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => strtoupper(Str::random(2)),
            'part_code' => strtoupper(Str::random(10)),
            'name' => $this->faker->name,
            'interior_code' => strtoupper(Str::random(4)),
            'vehicle_color_code' => strtoupper(Str::random(4)),
            'ecn_in' => strtoupper(Str::random(10)),
            'ecn_out' => strtoupper(Str::random(10)),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): PartColorFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
