<?php

namespace Database\Factories;

use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => strtoupper(Str::random(2)),
            'description' => $this->faker->text,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'forecast_by_week' => mt_rand(1, 50),
            'forecast_by_month' => mt_rand(1, 50),
            'receiver' => json_encode([$this->faker->email]),
            'bcc' => json_encode([$this->faker->email]),
            'cc' => json_encode([$this->faker->email]),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): SupplierFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
