<?php

namespace Database\Factories;

use App\Models\Remark;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

class RemarkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Remark::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'content' => $this->faker->text,
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function forModel($model): RemarkFactory
    {
        return $this->state(function (array $attributes) use ($model) {
            return [
                'modelable_type' => $model->getMorphClass(),
                'modelable_id' => $model->getKey()
            ];
        });
    }

    public function withDeleted(Carbon $date = null): RemarkFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
