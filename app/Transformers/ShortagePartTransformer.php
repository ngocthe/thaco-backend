<?php

namespace App\Transformers;

use App\Models\ShortagePart;
use League\Fractal\TransformerAbstract;

class ShortagePartTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected array $dates;

    /**
     * @var array
     */
    protected array $remarks;

    public function __construct($dates = [], $remarks = [])
    {
        $this->dates = $dates;
        $this->remarks = $remarks;
    }

    /**
     * @param ShortagePart $shortagePart
     * @return array
     */
    public function transform(ShortagePart $shortagePart): array
    {
        $key = implode('-', [
            $shortagePart->part_code, $shortagePart->part_color_code, $shortagePart->plant_code, $shortagePart->import_id
        ]);
        $remarks = $this->remarks[$key] ?? [];
        return [
			'part_code' => $shortagePart->part_code,
			'part_color_code' => $shortagePart->part_color_code,
            'plant_code' => $shortagePart->plant_code,
            'plan_dates' => $this->getProductionDates($shortagePart->days, $shortagePart->quantities, $remarks)
        ];
    }

    /**
     * @param $days
     * @param $quantities
     * @param $remarks
     * @return array
     */
    private function getProductionDates($days, $quantities, $remarks): array
    {
        $days = explode(',', $days);
        $quantities = explode(',', $quantities);
        $productionPlans = [];
        foreach ($this->dates as $date) {
            $productionPlans[$date['date']] = [
                'plan_date' => $date['date'],
                'day_off' => $date['day_off'],
                'quantity' => 0,
                'month_no' => $date['month_no'],
                'week_no' => $date['week_no'],
                'remarks' => $remarks[$date['date']] ?? []
            ];
        }
        foreach ($days as $key => $productionDate) {
            $productionPlans[$productionDate]['quantity'] = intval($quantities[$key]);
        }
        return array_values($productionPlans);
    }
}
