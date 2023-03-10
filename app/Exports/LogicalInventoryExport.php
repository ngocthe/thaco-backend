<?php

namespace App\Exports;

use App\Services\LogicalInventoryService;
use App\Services\MrpWeekDefinitionService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class LogicalInventoryExport implements FromView, WithProperties
{
    public function properties(): array
    {
        return [
            'title' => 'Logical Inventory'
        ];
    }

    public function view(): View
    {
        $type = request()->get('type', 'xls');
        list($weeks, $dates) = (new MrpWeekDefinitionService())->getWeekTitleAndDates(Carbon::now()->addDays(1)->toDateString());
        $rawLogicalInventories = (new LogicalInventoryService())->getForecastInventory($type == 'pdf');
        $productionDates = [];
        foreach ($rawLogicalInventories as $rawLogicalInventory) {
            $productionDates[] = [
                'part_code' => $rawLogicalInventory->part_code,
                'part_color_code' => $rawLogicalInventory->part_color_code,
                'production_dates' => $this->getProductionDates($rawLogicalInventory->days, $rawLogicalInventory->quantities, $dates)
            ];
        }

        return view('exports.logical-inventories', [
            'productionDates' => $productionDates,
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
    private function getProductionDates($days, $volumes, $dates): array
    {
        $days = explode(',', $days);
        $volumes = explode(',', $volumes);
        $productionDates = [];
        foreach ($dates as $date) {
            $productionDates[$date] = 0;
        }
        foreach ($days as $key => $productionDate) {
            $productionDate = Carbon::createFromFormat('Y-m-d', $productionDate)->format('d/m/Y');
            $productionDates[$productionDate] = $volumes[$key];
        }
        return $productionDates;
    }
}
