<?php

namespace App\Exports;

use App\Services\MrpResultService;
use App\Services\MrpWeekDefinitionService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class MrpResultByPartExport implements FromView, WithProperties
{
    public function properties(): array
    {
        return [
            'title' => 'MRP Result Part'
        ];
    }

    public function view(): View
    {
        $type = request()->get('type', 'xls');
        list($weeks, $dates) = (new MrpWeekDefinitionService())->getWeekTitleAndDates();
        request()->merge(['group_by' => 'day']);
        $rawMrpResults = (new MrpResultService())->getMrpResultsByPart($type == 'pdf');
        $productionDates = [];
        foreach ($rawMrpResults as $mrpResult) {
            $productionDates[] = [
                'part_code' => $mrpResult->part_code,
                'part_color_code' => $mrpResult->part_color_code,
                'production_dates' => $this->getProductionDates($mrpResult->days, $mrpResult->quantities, $dates)
            ];
        }
        return view('exports.mrp-result-by-part', [
            'productionDates' => $productionDates,
            'weeks' => $weeks,
            'dates' => $dates
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
