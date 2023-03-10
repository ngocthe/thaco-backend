<?php

namespace Database\Factories;

use App\Models\DefectInventory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class DefectInventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = DefectInventory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'box_id' => mt_rand(1, 5),
            'defect_id' => Arr::random(['W','D','X','S']),
            'part_defect_quantity' => mt_rand(1, 50),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function forModel($model): DefectInventoryFactory
    {
        return $this->state(function (array $attributes) use ($model) {
            return [
                'modelable_type' => $model->getMorphClass(),
                'modelable_id' => $model->getKey()
            ];
        });
    }

    public function withDeleted(Carbon $date = null): DefectInventoryFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
