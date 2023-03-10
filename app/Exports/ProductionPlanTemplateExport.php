<?php

namespace App\Exports;

use App\Models\MrpWeekDefinition;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class ProductionPlanTemplateExport implements FromView, WithProperties
{
    const MRP_MONTH_TITLES = [
        'N ( MRP Run Month )',
        'N+1',
        'N+2',
        'N+3 ( MRP Target Month )',
        'N+4 ( Forecast 1 )',
        'N+5 ( Forecast 2 )',
        'N+6 ( Forecast 3 )'
    ];

    public function properties(): array
    {
        return [
            'title' => 'MRP Import Production Schedule'
        ];
    }

    public function view(): View
    {
        list($months, $weeks, $dates) = $this->getMonthsWeekTitleAndDates();

        return view('exports.templates.production-plans', [
            'months' => $months,
            'weeks' => $weeks,
            'dates' => $dates,
        ]);
    }


    /**
     * @return array
     */
    private function getMonthsWeekTitleAndDates(): array
    {
        $firstDay = MrpWeekDefinition::query()
            ->select('date')
            ->where([
                'year' => Carbon::now()->year,
                'month_no' => Carbon::now()->month
            ])
            ->first()
            ->toArray();
        $afterYear = Carbon::now()->addMonths(6);
        $lastDay = MrpWeekDefinition::query()
            ->select('date')
            ->where([
                'year' => $afterYear->year,
                'month_no' => $afterYear->month
            ])
            ->orderBy('date', 'desc')
            ->first()
            ->toArray();

        $rawDates = MrpWeekDefinition::query()
            ->select('date', 'month_no', 'week_no', 'year', 'day_off')
            ->where('date', '>=', $firstDay['date'])
            ->where('date', '<=', $lastDay['date'])
            ->get()
            ->toArray();

        $months = [];
        $weeks = [];
        $dates = [];
        foreach ($rawDates as $date) {
            $keyMonth = $date['month_no'] . '/' . $date['year'];
            if (!isset($months[$keyMonth])) {
                $months[$keyMonth] = [
                    'name' => self::MRP_MONTH_TITLES[count($months)],
                    'format' => $keyMonth,
                    'weeks' => []
                ];
            }
            $keyWeek = $date['year'] . '-' .$date['month_no'] . '-' . $date['week_no'];
            if (!isset($weeks[$keyWeek])) {
                $weekTitle = 'MRP Week ' . $date['week_no'];
                $weeks[$keyWeek] = [
                    'title' => $weekTitle
                ];
                $months[$keyMonth]['weeks'][] = $weekTitle;
            }
            $format = explode('-', $date['date'])[2];
            $dates[] = [
                'format' => $format,
                'day_off' => $date['day_off']
            ];
        }

        return [array_values($months), $weeks, $dates];
    }
}
