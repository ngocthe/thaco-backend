<?php

namespace Database\Factories;

use App\Models\BoxType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BoxTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = BoxType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => strtoupper(Str::random(5)),
            'part_code' => strtoupper(Str::random(5)),
            'description' => $this->faker->text,
            'weight' => mt_rand(1, 99),
            'width' =>  mt_rand(1, 99),
            'height' =>  mt_rand(1, 99),
            'depth' =>  mt_rand(1, 99),
            'quantity' =>  mt_rand(1, 1000),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): BoxTypeFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
