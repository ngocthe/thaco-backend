<?php

namespace Database\Factories;

use App\Models\Msc;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MscFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Msc::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = CarbonImmutable::now();
        return [
            'code' => strtoupper(Str::random(7)),
            'description' => strtoupper($this->faker->text),
            'interior_color' => strtoupper($this->faker->colorName),
            'car_line' => strtoupper($this->faker->text(10)),
            'model_grade' => strtoupper($this->faker->text(10)),
            'body' => strtoupper($this->faker->text(20)),
            'engine' => strtoupper($this->faker->text(10)),
            'transmission' => strtoupper($this->faker->text(10)),
            'plant_code' => strtoupper(Str::random(5)),
            'effective_date_in' => $now,
            'effective_date_out' => $now,
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): MscFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
