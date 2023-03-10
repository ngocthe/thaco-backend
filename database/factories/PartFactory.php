<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class PartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => strtoupper(Str::random(10)),
            'name' => $this->faker->name,
            'group' => strtoupper(Str::random(2)),
            'ecn_in' => strtoupper(Str::random(10)),
            'ecn_out' => strtoupper(Str::random(2)),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): PartFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
