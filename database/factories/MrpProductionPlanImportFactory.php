<?php

namespace Database\Factories;

use App\Models\MrpProductionPlanImport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MrpProductionPlanImportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = MrpProductionPlanImport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'file_path' => Str::random(50),
            'original_file_name' => Str::random(50),
            'mrp_or_status' => Arr::random([
                MrpProductionPlanImport::STATUS_NOT_RUN,
                MrpProductionPlanImport::STATUS_CHECKED_SHORTAGE,
                MrpProductionPlanImport::STATUS_RAN_MRP,
                MrpProductionPlanImport::STATUS_CAN_RUN_ORDER
            ]),
            'mrp_or_progress' => rand(1, 100),
            'mrp_or_result' => Str::random(50),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): MrpProductionPlanImportFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
