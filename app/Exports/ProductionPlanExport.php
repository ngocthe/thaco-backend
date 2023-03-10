<?php

namespace App\Exports;

use App\Models\MrpWeekDefinition;
use App\Services\MrpWeekDefinitionService;
use App\Services\ProductionPlanService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class ProductionPlanExport implements FromView, WithProperties
{
    public function properties(): array
    {
        return [
            'title' => 'Production Plan'
        ];
    }

    public function view(): View
    {
        $type = request()->get('type', 'xls');
        list($weeks, $dates) = (new MrpWeekDefinitionService())->getWeekTitleAndDates();
        $rawProductionPlans = (new ProductionPlanService())->filterProductionPlant($type == 'pdf');
        $productionPlans = [];
        foreach ($rawProductionPlans as $plan) {
            $productionPlans[] = [
                'msc_code' => $plan->msc_code,
                'msc_description' => $plan->msc->description,
                'vehicle_color_code' => $plan->vehicle_color_code,
                'plant_code' => $plan->plant_code,
                'production_plans' => $this->getProductionPlansData($plan->days, $plan->volumes, $dates)
            ];
        }
        return view('exports.production-plans', [
            'productionPlans' => $productionPlans,
            'weeks' => $weeks,
            'dates' => $dates,
            'type' => $type
        ]);
    }

    /**
     * @param $days
     * @param $volumes
     * @param $dates
     * @return array
     */
    private function getProductionPlansData($days, $volumes, $dates): array
    {
        $days = explode(',', $days);
        $volumes = explode(',', $volumes);
        $productionPlans = [];
        foreach ($dates as $date) {
            $productionPlans[$date] = 0;
        }
        foreach ($days as $key => $plan_date) {
            $plan_date = Carbon::createFromFormat('Y-m-d', $plan_date)->format('d/m/Y');
            $productionPlans[$plan_date] = $volumes[$key];
        }
        return $productionPlans;
    }
}
