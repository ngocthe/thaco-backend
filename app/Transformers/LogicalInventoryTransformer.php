<?php

namespace App\Transformers;

use App\Models\LogicalInventory;
use League\Fractal\TransformerAbstract;

class LogicalInventoryTransformer extends TransformerAbstract
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
     * @param LogicalInventory $logicalInventory
     * @return array
     */
    public function transform(LogicalInventory $logicalInventory): array
    {
        return [
			'part_code' => $logicalInventory->part_code,
			'part_color_code' => $logicalInventory->part_color_code,
            'production_dates' => $this->getProductionDates($logicalInventory->days, $logicalInventory->quantities)
        ];
    }

    /**
     * @param $days
     * @param $volumes
     * @return array
     */
    private function getProductionDates($days, $volumes): array
    {
        $days = explode(',', $days);
        $volumes = explode(',', $volumes);
        $productionPlans = [];
        foreach ($this->dates as $date => $dayOff) {
            $productionPlans[$date] = [
                'production_date' => $date,
                'day_off' => $dayOff,
                'quantity' => 0
            ];
        }
        foreach ($days as $key => $productionDate) {
            $productionPlans[$productionDate]['quantity'] = intval($volumes[$key]);
        }
        return array_values($productionPlans);
    }
}
