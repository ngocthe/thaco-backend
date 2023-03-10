<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Passport\Token;

class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Token::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->regexify('[a-z0-9]{80}'),
            'user_id' => function () {
                return User::factory()->create()->id;
            },
            'client_id' => $this->faker->randomNumber(),
            'name' => null,
            'scopes' => '["*"]',
            'revoked' => false,
            'expires_at' => $this->faker->dateTime()
        ];
    }

    public function withClient(int $clientId): TokenFactory
    {
        return $this->state(function (array $attributes) use ($clientId) {
            return [
                'client_id' => $clientId,
            ];
        });
    }
}
