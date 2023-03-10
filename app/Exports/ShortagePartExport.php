<?php

namespace App\Exports;

use App\Services\MrpWeekDefinitionService;
use App\Services\ShortagePartService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class ShortagePartExport implements FromView, WithProperties
{
    public function properties(): array
    {
        return [
            'title' => 'Shortage Part'
        ];
    }

    public function view(): View
    {
        $type = request()->get('type', 'xls');
        list($weeks, $dates) = (new MrpWeekDefinitionService())->getWeekTitleAndDates();
        $rawLogicalInventories = (new ShortagePartService())->filterShortagePart($type == 'pdf');

        $planDates = [];
        foreach ($rawLogicalInventories as $rawLogicalInventory) {
            $planDates[] = [
                'part_code' => $rawLogicalInventory->part_code,
                'part_color_code' => $rawLogicalInventory->part_color_code,
                'plan_dates' => $this->getPlanDates($rawLogicalInventory->days, $rawLogicalInventory->quantities, $dates)
            ];
        }

        return view('exports.shortage-parts', [
            'planDates' => $planDates,
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
    private function getPlanDates($days, $volumes, $dates): array
    {
        $days = explode(',', $days);
        $volumes = explode(',', $volumes);
        $planDates = [];
        foreach ($dates as $date) {
            $planDates[$date] = 0;
        }
        foreach ($days as $key => $productionDate) {
            $productionDate = Carbon::createFromFormat('Y-m-d', $productionDate)->format('d/m/Y');
            $planDates[$productionDate] = $volumes[$key];
        }
        return $planDates;
    }
}
