<?php

namespace App\Exports;

use App\Services\MrpResultService;
use App\Services\MrpWeekDefinitionService;
use App\Services\ProductionPlanService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class MrpResultByMscExport implements FromView, WithProperties
{
    public function properties(): array
    {
        return [
            'title' => 'MRP Result'
        ];
    }

    public function view(): View
    {
        $type = request()->get('type', 'xls');
        list($weeks, $dates) = (new MrpWeekDefinitionService())->getWeekTitleAndDates();
        request()->merge(['group_by' => 'day']);
        $mrpService = new MrpResultService();
        $rawMrpResults = $mrpService->getMrpResultsByMSC($type == 'pdf');
        $mscVolume = $mrpService->getProductionPlanVolume($rawMrpResults);
        $mscData = [];

        foreach ($mscVolume as $mscCode => $volumeByDate) {
            $mscData[$mscCode] = [
                'msc_code' => $mscCode,
                'days' => $this->getProductionVolumeByDate($volumeByDate, $dates),
                'data' => []
            ];
        }
        foreach ($rawMrpResults as $rawMrpResult) {
            $mscData[$rawMrpResult->msc_code]['data'][] = [
                'msc_code' => $rawMrpResult->msc_code,
                'vehicle_color_code' => $rawMrpResult->vehicle_color_code,
                'part_code' => $rawMrpResult->part_code,
                'part_color_code' => $rawMrpResult->part_color_code,
                'production_dates' => $this->getProductionDates($rawMrpResult->days, $rawMrpResult->quantities, $dates)
            ];
        }
        return view('exports.mrp-result-by-msc', [
            'mscData' => $mscData,
            'weeks' => $weeks,
            'dates' => $dates
        ]);
    }

    /**
     * @param $volumes
     * @param $dates
     * @return array
     */
    private function getProductionVolumeByDate($volumes, $dates): array
    {
        $arrayDateVolume = [];
        foreach ($volumes as $item) {
            $date = Carbon::createFromFormat('Y-m-d', $item['production_date'])->format('d/m/Y');
            $arrayDateVolume[$date] = $item['volume'];
        }
        $volumeByDate = [];
        foreach ($dates as $date) {
            $volumeByDate[$date] = $arrayDateVolume[$date] ?? 0;
        }
        return $volumeByDate;
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
