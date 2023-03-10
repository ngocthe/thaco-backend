<?php

namespace App\Transformers;

use App\Models\ProductionPlan;
use League\Fractal\TransformerAbstract;

class ProductionPlanTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected array $dates;

    public function __construct($dates = [])
    {
        $this->dates = $dates;
    }

    /**
     * @param ProductionPlan $productionPlan
     * @return array
     */
    public function transform(ProductionPlan $productionPlan): array
    {
        return [
			'msc_code' => $productionPlan->msc_code,
            'msc_description' => $productionPlan->msc->description,
			'vehicle_color_code' => $productionPlan->vehicle_color_code,
            'plant_code' => $productionPlan->plant_code,
            'production_plans' => $this->getProductionPlans($productionPlan->days, $productionPlan->volumes)
        ];
    }

    /**
     * @param $days
     * @param $volumes
     * @return array
     */
    private function getProductionPlans($days, $volumes): array
    {
        $days = explode(',', $days);
        $volumes = explode(',', $volumes);
        $productionPlans = [];
        foreach ($this->dates as $date => $day_off) {
            $productionPlans[$date] = [
                'plant_date' => $date,
                'day_off' => $day_off,
                'volume' => 0
            ];
        }
        foreach ($days as $key => $plan_date) {
            $productionPlans[$plan_date]['volume'] = intval($volumes[$key]);
        }
        return array_values($productionPlans);
    }
}
