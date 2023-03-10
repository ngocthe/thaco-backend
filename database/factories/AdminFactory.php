<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'code' => Str::random(mt_rand(4, 12)),
            'name' => Str::random(mt_rand(4, 12)),
            'username' => Str::random(mt_rand(4, 12)),
            'email' => $this->faker->unique()->safeEmail(),
            'company' => $this->faker->company,
            'email_verified_at' => now(),
            'password' => Hash::make($this->faker->password()),
            'remember_token' => Str::random(10)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return AdminFactory
     */
    public function unverified(): AdminFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function preRegister(): AdminFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
                'password' => $this->faker->password(16),
                'remember_token' => null,
            ];
        });
    }

    public function withPassword(string $password): AdminFactory
    {
        return $this->state(function (array $attributes) use ($password) {
            return [
                'password' => Hash::make($password)
            ];
        });
    }

    public function withDeleted(Date $date = null): AdminFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
