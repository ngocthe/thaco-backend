<?php

namespace App\Exports;

use App\Models\MrpWeekDefinition;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class PartUsageResultTemplateExport implements FromView, WithProperties
{
    public function properties(): array
    {
        return [
            'title' => 'Inventory Control Import Production Results'
        ];
    }

    public function view(): View
    {
        list($months, $weeks, $dates) = $this->getMonthsWeekTitleAndDates();

        return view('exports.templates.part-usage-result', [
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
        $prev3Months = Carbon::now()->subMonths(3);
        $firstDay = MrpWeekDefinition::query()
            ->select('date')
            ->where([
                'year' => $prev3Months->year,
                'month_no' => $prev3Months->month
            ])
            ->first()
            ->toArray();
        $lastDay = MrpWeekDefinition::query()
            ->select('date')
            ->where([
                'year' => Carbon::now()->year,
                'month_no' => Carbon::now()->month
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
