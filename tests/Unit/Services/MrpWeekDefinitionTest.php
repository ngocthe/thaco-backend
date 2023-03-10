<?php

namespace Tests\Unit\Services;

use App\Exports\WarehouseInventorySummaryExport;
use App\Exports\WarehouseInventorySummaryGroupByPartExport;
use App\Models\Admin;
use App\Models\MrpWeekDefinition;
use App\Models\Plant;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Services\AuthService;
use App\Services\MrpWeekDefinitionService;
use App\Services\WarehouseInventorySummaryService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MrpWeekDefinitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AuthService
     */
    private $mrpWDService;

    public function setUp(): void
    {
        parent::setUp();
        $admin = Admin::factory()->create();
        Auth::login($admin);
        $this->mrpWDService = new MrpWeekDefinitionService();
    }

    public function test_model()
    {
        $this->assertEquals(MrpWeekDefinition::class, $this->mrpWDService->model(), 'Base Service model() does not return correct Model Instance');
    }

    /**
     * @return void
     */
    public function test_paginate()
    {

        $params = [
            'page' => 1,
            'per_page' => 20
        ];
        MrpWeekDefinition::factory()->count(20)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(MrpWeekDefinition::class, $this->mrpWDService, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    /**
     * @return void
     */
    public function test_paginate_has_search()
    {
        $mrpWD = MrpWeekDefinition::factory()->create();
        MrpWeekDefinition::factory()->count(9)->create();
        $year = $mrpWD['year'];
        $month = $mrpWD['month_no'];
        $params = [
            'page' => 1,
            'per_page' => 20,
            'year' => $year,
            'month' => $month,
        ];

        $listItemQuery = MrpWeekDefinition::query();

        if ($month == 1) {
            $listItemQuery->where(function ($q) use ($year) {
                $q->where(function ($q_) use ($year) {
                    $q_->where('year', $year)
                        ->whereIn('month_no', [1, 2]);
                })->orWhere(function ($q_) use ($year) {
                    $q_->where([
                        'year' => $year - 1,
                        'month_no' => 12
                    ]);
                });
            });
        } elseif ($month == 12) {
            $listItemQuery->where(function ($q) use ($year) {
                $q->where(function ($q_) use ($year) {
                    $q_->where('year', $year)
                        ->whereIn('month_no', [11, 12]);
                })->orWhere(function ($q_) use ($year) {
                    $q_->where([
                        'year' => $year + 1,
                        'month_no' => 1
                    ]);
                });
            });
        } else {
            $listItemQuery->where('year', $mrpWD['year'])
                ->whereIn('month_no', [$mrpWD['month_no'] - 1, $mrpWD['month_no'], $mrpWD['month_no'] + 1]);
        }

        $listItemQuery = $listItemQuery
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(MrpWeekDefinition::class, $this->mrpWDService, $params, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_store_when_has_date()
    {
        $mrpWD = MrpWeekDefinition::factory()->create();

        $data = Arr::only($mrpWD->getAttributes(), [
            'date',
            'day_off',
            'year',
            'month_no',
            'week_no'
        ]);

        $mrpWDCreated = $this->mrpWDService->store($data);

        $this->assertInstanceOf(MrpWeekDefinition::class, $mrpWDCreated);
        $this->assertEquals($mrpWD->day_off, !$mrpWDCreated->day_off);
        $this->assertDatabaseHas('mrp_week_definitions', Arr::only($mrpWDCreated->getAttributes(), $mrpWDCreated->getFillable()));
    }

    public function test_store()
    {
        $now = CarbonImmutable::now();
        $dateString = $now->format('Y-m-d');
        list($monthNo, $weekNo, $year) = $this->getMonthWeekNoOfDate($dateString);
        $mrpWD = MrpWeekDefinition::factory()->sequence([
            'month_no' => $monthNo,
            'week_no' => $weekNo,
            'year' => $year
        ])->make();

        $data = Arr::only($mrpWD->getAttributes(), [
            'date',
            'day_off',
            'year',
            'month_no',
            'week_no'
        ]);

        $mrpWDCreated = $this->mrpWDService->store($data);

        $this->assertInstanceOf(MrpWeekDefinition::class, $mrpWDCreated);
        $this->assertArraySubset(Arr::only($mrpWD->getAttributes(), $mrpWD->getFillable()), $mrpWDCreated->getAttributes());
        $this->assertDatabaseHas('mrp_week_definitions', Arr::only($mrpWDCreated->getAttributes(), $mrpWDCreated->getFillable()));
    }

    private function getMonthWeekNoOfDate($dateString): array
    {
        $date = Carbon::parse($dateString);
        $weekNo = $date->weekNumberInMonth;
        $year = $date->year;
        $monthNo = $date->month;
        $lastOfMonth = Carbon::parse($dateString)->lastOfMonth();
        $prevLastOfMonth = Carbon::parse($dateString)->subMonthsNoOverflow()->lastOfMonth();
        $prevFirstOfMonth = Carbon::parse($dateString)->subMonthsNoOverflow()->firstOfMonth();

        if ($prevLastOfMonth->dayOfWeek < $this->mrpWDService::DAYS_OF_WEEK) {
            if ($weekNo >= 5) {
                if ($lastOfMonth->dayOfWeek < $this->mrpWDService::DAYS_OF_WEEK) {
                    $weekNo = 1;
                    $monthNo += 1;
                }
            }
        } else {
            if ($weekNo == 1) {
                $monthNo -= 1;
                if ($prevFirstOfMonth->dayOfWeek <= $this->mrpWDService::DAYS_OF_WEEK) {
                    $weekNo = 5;
                } else {
                    $weekNo = 4;
                }
            } elseif ($weekNo == 5) {
                if ($prevLastOfMonth->dayOfWeekIso >= $this->mrpWDService::DAYS_OF_WEEK) {
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

}
