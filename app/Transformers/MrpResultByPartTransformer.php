<?php

namespace App\Transformers;

use App\Models\MrpResult;
use League\Fractal\TransformerAbstract;

class MrpResultByPartTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected array $dates;

    /**
     * @var
     */
    protected $groupBy;

    public function __construct($dates = [], $groupBy = null)
    {
        $this->dates = $dates;
        $this->groupBy = $groupBy;
    }

    /**
     * @param MrpResult $mrpResult
     * @return array
     */
    public function transform(MrpResult $mrpResult): array
    {
        return [
            'part_code' => $mrpResult->part_code,
            'part_color_code' => $mrpResult->part_color_code,
            'plant_code' => $mrpResult->plant_code,
            'production_dates' => $this->getMrpResults($mrpResult)
        ];
    }

    /**
     * @param $mrpResult
     * @return array
     */
    private function getMrpResults($mrpResult): array
    {
        if ($this->groupBy == 'month') {
            return $this->getProductionMonths($mrpResult->months, $mrpResult->quantities);
        } elseif ($this->groupBy == 'week') {
            return $this->getProductionWeeks($mrpResult->weeks, $mrpResult->quantities);
        } else {
            return $this->getProductionDates($mrpResult->days, $mrpResult->quantities);
        }
    }

    /**
     * @param $days
     * @param $quantities
     * @return array
     */
    private function getProductionDates($days, $quantities): array
    {
        $days = explode(',', $days);
        $quantities = explode(',', $quantities);
        $productionPlans = [];
        foreach ($this->dates as $date) {
            $productionPlans[$date['date']] = [
                'production_date' => $date['date'],
                'day_off' => $date['day_off'],
                'quantity' => 0,
                'month_no' => $date['month_no'],
                'week_no' => $date['week_no']
            ];
        }
        foreach ($days as $key => $productionDate) {
            $productionPlans[$productionDate]['quantity'] += intval($quantities[$key]);
        }
        return array_values($productionPlans);
    }

    /**
     * @param $weeks
     * @param $quantities
     * @return array
     */
    private function getProductionWeeks($weeks, $quantities): array
    {
        $weeks = explode(',', $weeks);
        $quantities = explode(',', $quantities);
        $productionPlans = [];
        foreach ($this->dates as $week) {
            $productionPlans[$week] = [
                'week_no' => $week,
                'quantity' => 0
            ];
        }
        foreach ($weeks as $key => $week) {
            $productionPlans[$week]['quantity'] += intval($quantities[$key]);
        }
        return array_values($productionPlans);
    }

    /**
     * @param $months
     * @param $quantities
     * @return array
     */
    private function getProductionMonths($months, $quantities): array
    {
        $months = explode(',', $months);
        $quantities = explode(',', $quantities);
        $productionPlans = [];
        for ($i = 1; $i <= 12; $i++) {
            $productionPlans[$i] = [
                'quantity' => 0,
                'month_no' => $i
            ];
        }
        foreach ($months as $key => $month) {
            $productionPlans[$month]['quantity'] += intval($quantities[$key]);
        }
        return array_values($productionPlans);
    }
}
