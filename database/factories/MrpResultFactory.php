<?php

namespace Database\Factories;

use App\Models\MrpResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MrpResultFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = MrpResult::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'production_date' => now()->format('Y-m-d'),
            'msc_code' => Str::random(7),
            'vehicle_color_code' => Str::random(4),
            'production_volume' => mt_rand(1, 100),
            'part_code' => strtoupper(Str::random(10)),
            'part_color_code' => strtoupper(Str::random(2)),
            'part_requirement_quantity' => mt_rand(1, 100),
            'import_id' => mt_rand(1, 100),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): MrpResultFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
