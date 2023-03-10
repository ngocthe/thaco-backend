<?php

namespace App\Services;

use App\Models\MrpWeekDefinition;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MrpWeekDefinitionService extends BaseService
{
    const DAYS_OF_WEEK = 3;

    /**
     * @return string
     */
    public function model(): string
    {
        return MrpWeekDefinition::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        if (isset($params['year']) && $this->checkParamYearFilter($params['year'])) {
            $year = $params['year'];
        } else {
            $year = Carbon::now()->year;
        }

        if (isset($params['month']) && $this->checkParamMonthFilter($params['month'])) {
            $month = $params['month'];
        } else {
            $month = Carbon::now()->month;
        }

        if (!empty($params['extract_month']) && (bool)$params['extract_month']) {
            $this->query
                ->where('year', $year)
                ->where('month_no', $month);
        } else {
            if ($month == 1) {
                $this->query
                    ->where(function($q) use ($year) {
                        $q->where(function($q_) use ($year) {
                            $q_->where('year', $year)
                                ->whereIn('month_no', [1, 2]);
                        })->orWhere(function($q_) use ($year) {
                            $q_->where([
                                'year' => $year - 1,
                                'month_no' => 12
                            ]);
                        });
                    });
            } elseif ($month == 12) {
                $this->query
                    ->where(function($q) use ($year) {
                        $q->where(function($q_) use ($year) {
                            $q_->where('year', $year)
                                ->whereIn('month_no', [11, 12]);
                        })->orWhere(function($q_) use ($year) {
                            $q_->where([
                                'year' => $year + 1,
                                'month_no' => 1
                            ]);
                        });
                    });
            } else {
                $this->query
                    ->where('year', $year)
                    ->whereIn('month_no', [$month - 1, $month, $month + 1]);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @param bool $hasRemark
     * @return Builder|Model
     * @throws Exception
     */
    public function store(array $attributes, bool $hasRemark = true)
    {
        /**
         * @var MrpWeekDefinition $date ;
         */
        $date = $this->query->where('date', $attributes['date'])->first();
        if ($date) {
            $date->day_off = !$date->day_off;
            $date->save();
            return $date;
        } else {
            list($monthNo, $weekNo, $year) = $this->getMonthWeekNoOfDate($attributes['date']);
            $attributes['month_no'] = $monthNo;
            $attributes['week_no'] = $weekNo;
            return $this->query->create($attributes);
        }
    }

    /**
     * @param $dateString
     * @return array
     */
    public function getMonthWeekNoOfDate($dateString): array
    {
        $date = Carbon::parse($dateString);
        $weekNo = $date->weekNumberInMonth;
        $year = $date->year;
        $monthNo = $date->month;
        $lastOfMonth = Carbon::parse($dateString)->lastOfMonth();
        $prevLastOfMonth = Carbon::parse($dateString)->subMonthsNoOverflow()->lastOfMonth();
        $prevFirstOfMonth = Carbon::parse($dateString)->subMonthsNoOverflow()->firstOfMonth();

        // TH tuan cuoi cua thang truoc co so ngay < 3 => thang nay bat dau tu tuan 1
        if ($prevLastOfMonth->dayOfWeek < self::DAYS_OF_WEEK) {
            // neu la tuan cuoi cua thang
            if ($weekNo >= 5) {
                if ($lastOfMonth->dayOfWeek < self::DAYS_OF_WEEK) {
                    $weekNo = 1;
                    $monthNo += 1;
                }
            }
        } else {
            if ($weekNo == 1) {
                $monthNo -= 1;
                if ($prevFirstOfMonth->dayOfWeek <= self::DAYS_OF_WEEK) {
                    $weekNo = 5;
                } else {
                    $weekNo = 4;
                }
            } elseif ($weekNo == 5) {
                if ($prevLastOfMonth->dayOfWeekIso >= self::DAYS_OF_WEEK) {
                    $weekNo -= 1;
                }
            } elseif ($weekNo == 6) {
                $weekNo = 1;
                $monthNo += 1;
            } else {
                $weekNo -= 1;
            }
        }
        if ($monthNo == 0) {
            $monthNo = 12;
            $year -= 1;
        }
        return [$monthNo, $weekNo, $year];
    }

    /**
     * @param null $date
     * @param bool $returnWeekMonthNo
     * @return array
     */
    public function getDates($date = null, bool $returnWeekMonthNo = false): array
    {
        $year = request()->get('year') ?: Carbon::now()->year;
        $month = request()->get('month') ?: Carbon::now()->month;
        $query = MrpWeekDefinition::query()
            ->select('date', 'day_off', 'month_no', 'week_no')
            ->where(['year' => $year, 'month_no' => $month]);

        if ($date) {
            $query->where('date', '>=', $date);
        }

        if ($returnWeekMonthNo) {
            return $query
                ->get()
                ->toArray();
        } else {
            return $query
                ->pluck('day_off', 'date')
                ->toArray();
        }

    }

    /**
     * @return array
     */
    public function getWeeks(): array
    {
        $year = request()->get('year') ?: Carbon::now()->year;
        $month = request()->get('month') ?: Carbon::now()->month;
        return MrpWeekDefinition::query()
            ->distinct()
            ->select('week_no')
            ->where([
                'year' => $year,
                'month_no' => $month
            ])
            ->orderBy('week_no')
            ->pluck('week_no')
            ->toArray();
    }

    /**
     * @param null $date
     * @return array
     */
    public function getWeekTitleAndDates($date = null): array
    {
        $year = request()->get('year') ?: Carbon::now()->year;
        $month = request()->get('month') ?: Carbon::now()->month;
        $query = MrpWeekDefinition::query()
            ->select('date', 'month_no', 'week_no')
            ->where([
                'year' => $year,
                'month_no' => $month
            ]);

        if ($date) {
            $query->where('date', '>=', $date);
        }

        $rawDates = $query->get()->toArray();

        $weeks = [];
        $dates = [];
        foreach ($rawDates as $date) {
            $key = $date['month_no'] . '-' . $date['week_no'];
            if (!isset($weeks[$key])) {
                $weekTitle = $this->getWeekTitle($date['week_no'], $date['month_no'] != $month ? $date['month_no'] : null);
                $weeks[$key] = [
                    'title' => $weekTitle,
                    'dates' => []
                ];
            }
            $date = Carbon::createFromFormat('Y-m-d', $date['date'])->format('d/m/Y');
            $weeks[$key]['dates'][] = $date;
            $dates[] = $date;
        }

        return [$weeks, $dates];
    }

    /**
     * @param $weekNo
     * @param $monthNo
     * @return string
     */
    private function getWeekTitle($weekNo, $monthNo = null): string
    {
        $weekTitle = 'Week ' . $weekNo;
        if ($monthNo) {
            $weekTitle .= ' (' . Carbon::create(0, $monthNo)->monthName . ')';
        }
        return $weekTitle;
    }
}
