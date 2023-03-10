<?php

namespace Tests\Unit\Services;

use App\Exports\LogicalInventoryExport;
use App\Models\Admin;
use App\Models\LogicalInventory;
use App\Models\MrpWeekDefinition;
use App\Models\Part;
use App\Models\WarehouseInventorySummary;
use App\Services\AuthService;
use App\Services\LogicalInventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogicalInventoryTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    /**
     * @var AuthService
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();
        $admin = Admin::factory()->create();
        Auth::login($admin);
        $this->service = new LogicalInventoryService();
    }

    public function test_model()
    {
        $this->assertEquals(LogicalInventory::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
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
        LogicalInventory::factory()->count(20)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(LogicalInventory::class, $this->service, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    /**
     * @return void
     */
    public function test_paginate_has_search()
    {
        $logicalInventory = LogicalInventory::factory()->create();
        $part = Part::factory()
            ->sequence(fn($sequence) => [
                'code' => $logicalInventory['part_code']
            ])->create();
        $params = [
            'part_code' => $logicalInventory['part_code'],
            'part_color_code' => $logicalInventory['part_color_code'],
            'plant_code' => $logicalInventory['plant_code'],
            'date' => $logicalInventory['production_date'],
            'received_date' => $logicalInventory['production_date'],
            'part_group' => $part['group'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        $listItemQuery = LogicalInventory::query()
            ->where('logical_inventories.part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('logical_inventories.part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('logical_inventories.plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->whereDate('logical_inventories.production_date', '=', $params['date'])
            ->whereDate('logical_inventories.production_date', '=', $params['received_date'])
            ->leftJoin('parts', function ($join) {
                $join->on('logical_inventories.part_code', '=', 'parts.code');
            })
            ->where('parts.group', 'LIKE', '%' . $params['part_group'] . '%')
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('logical_inventories.id')
            ->get();

        $listItemService = $this->paginate($params);

        $instanceModel = new LogicalInventory();
        $fillables = $instanceModel->getFillable();

        $dataQuery = $listItemQuery->toArray();
        $dataService = $listItemService->toArray()['data'];

        foreach ($dataQuery as $key => $val) {
            $dataQuery[$key] = Arr::only($val, $fillables);
        }

        foreach ($dataService as $key => $val) {
            $dataService[$key] = Arr::only($val, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    private function paginate($params = null, array $relations = [], bool $withTrashed = false): LengthAwarePaginator
    {
        $params = $params ?: request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        $this->service->buildBasicQuery($params, $relations, $withTrashed);
        return $this->service->query->latest('logical_inventories.id')->paginate($limit);
    }

    public function test_columns()
    {
        $logicalInventory = LogicalInventory::factory()->count(self::NUMBER_RECORD)->create();
        $logicalInventoryFillable = $logicalInventory->first()->getFillable();
        $column = Arr::random($logicalInventoryFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $logicalInventory->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $bwhInventoryQuery = LogicalInventory::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($bwhInventoryQuery, $result);
    }

    public function test_current_summary()
    {
        $warehouseInv = WarehouseInventorySummary::factory()->create();
        $logicalInv = LogicalInventory::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $warehouseInv['part_code'],
                'part_color_code' => $warehouseInv['part_color_code']
            ])
            ->count(self::NUMBER_RECORD)
            ->create();
        $params = [
            'part_code' => $warehouseInv['part_code'],
            'part_color_code' => $warehouseInv['part_color_code'],
            'plant_code' => $logicalInv[0]['plant_code'],
            'per_page' => self::NUMBER_RECORD
        ];
        request()->merge($params);
        $isPaginate = Arr::random([true, false]);
        $inventoryService = $this->service->getCurrentSummary($isPaginate);
        $inventoryQuery = LogicalInventory::query()->selectRaw("
                logical_inventories.part_code,
                logical_inventories.part_color_code,
                logical_inventories.plant_code,
                GROUP_CONCAT(logical_inventories.quantity SEPARATOR ',') as logical_quantities,
                GROUP_CONCAT(warehouse_inventory_summaries.quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(warehouse_inventory_summaries.warehouse_type SEPARATOR ',') as warehouse_types
            ")
            ->leftJoin('warehouse_inventory_summaries', function ($join) {
                $join->on('logical_inventories.part_code', '=', 'warehouse_inventory_summaries.part_code')
                    ->on('logical_inventories.part_color_code', '=', 'warehouse_inventory_summaries.part_color_code');
            })
            ->where('logical_inventories.part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('logical_inventories.part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('logical_inventories.plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->groupBy(['logical_inventories.part_code', 'logical_inventories.part_color_code', 'logical_inventories.plant_code'])
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        $fillables = ['part_code', 'part_color_code', 'plant_code', 'logical_quantities', 'quantities', 'warehouse_types'];
        if ($isPaginate) {
            $dataService = $inventoryService->toArray()['data'];
            foreach ($dataService as $key => $val) {
                $dataService[$key] = Arr::only($val, $fillables);
            }
            $this->assertInstanceOf(LengthAwarePaginator::class, $inventoryService);
            $this->assertEquals($inventoryQuery->toArray(), $dataService);
        } else {
            $dataQuery = $inventoryQuery->toArray();
            $dataService = $inventoryService->toArray();
            foreach ($dataQuery as $key => $val) {
                $dataQuery[$key] = Arr::only($val, $fillables);
            }

            foreach ($dataService as $key => $val) {
                $dataService[$key] = Arr::only($val, $fillables);
            }
            $this->assertEquals($dataQuery, $dataService);
        }
    }

    public function test_forecast_inventory_has_paginate()
    {
        $logicalInv = LogicalInventory::factory()
            ->sequence(fn($sequence) => [
                'production_date' => Carbon::now()->addDay(2)->toDateString()
            ])
            ->count(self::NUMBER_RECORD)
            ->create();
        $params = [
            'part_code' => $logicalInv[0]['part_code'],
            'part_color_code' => $logicalInv[0]['part_color_code'],
            'plant_code' => $logicalInv[0]['plant_code'],
            'per_page' => self::NUMBER_RECORD,
            'year' => Carbon::now()->year,
            'month' => Carbon::now()->month
        ];
        request()->merge($params);
        MrpWeekDefinition::factory()->create();
        $inventoryService = $this->service->getForecastInventory();
        $dateTime = $this->getDateTime($params);
        $inventoryQuery = $this->service->query->selectRaw("
                part_code,
                part_color_code,
                logical_inventories.plant_code,
                GROUP_CONCAT(quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(production_date SEPARATOR ',') as days
            ")
            ->where('logical_inventories.part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('logical_inventories.part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('logical_inventories.plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->where('production_date', '>=', Carbon::now()->addDay()->toDateString())
            ->where('production_date', '>=', $dateTime->firstOfMonth()->toDateString())
            ->where('production_date', '<=', $dateTime->lastOfMonth()->toDateString())
            ->groupBy(['part_code', 'part_color_code', 'logical_inventories.plant_code'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();

        $fillables = ['part_code', 'part_color_code', 'plant_code', 'quantities', 'days'];
        $dataQuery = $inventoryQuery->toArray();
        foreach ($dataQuery as $key => $val) {
            $dataQuery[$key] = Arr::only($val, $fillables);
        }
        $dataService = $inventoryService->toArray()['data'];
        foreach ($dataService as $key => $val) {
            $dataService[$key] = Arr::only($val, $fillables);
        }
        $this->assertInstanceOf(LengthAwarePaginator::class, $inventoryService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_forecast_inventory_case_has_not_paginate()
    {
        $logicalInv = LogicalInventory::factory()
            ->sequence(fn($sequence) => [
                'production_date' => Carbon::now()->addDay(2)->toDateString()
            ])
            ->count(self::NUMBER_RECORD)
            ->create();
        $params = [
            'part_code' => $logicalInv[0]['part_code'],
            'part_color_code' => $logicalInv[0]['part_color_code'],
            'plant_code' => $logicalInv[0]['plant_code'],
            'per_page' => self::NUMBER_RECORD,
            'year' => Carbon::now()->year,
            'month' => Carbon::now()->month
        ];
        request()->merge($params);

        MrpWeekDefinition::factory()->create();
        $inventoryService = $this->service->getForecastInventory(false);
        $dateTime = $this->getDateTime($params);
        $inventoryQuery = $this->service->query->selectRaw("
                part_code,
                part_color_code,
                logical_inventories.plant_code,
                GROUP_CONCAT(quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(production_date SEPARATOR ',') as days
            ")
            ->where('logical_inventories.part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('logical_inventories.part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('logical_inventories.plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->where('production_date', '>=', Carbon::now()->addDay()->toDateString())
            ->where('production_date', '>=', $dateTime->firstOfMonth()->toDateString())
            ->where('production_date', '<=', $dateTime->lastOfMonth()->toDateString())
            ->groupBy(['part_code', 'part_color_code', 'logical_inventories.plant_code'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();

        $fillables = ['part_code', 'part_color_code', 'plant_code', 'quantities', 'days'];
        $dataQuery = $inventoryQuery->toArray();
        foreach ($dataQuery as $key => $val) {
            $dataQuery[$key] = Arr::only($val, $fillables);
        }
        $dataService = $inventoryService->toArray();
        foreach ($dataService as $key => $val) {
            $dataService[$key] = Arr::only($val, $fillables);
        }
        $this->assertEquals($dataQuery, $dataService);
    }

    private function getDateTime($params)
    {
        $year = $params['year'] ?: Carbon::now()->year;
        $month = $params['month'] ?: Carbon::now()->month;
        return Carbon::create($year, $month);
    }

    public function test_export()
    {
        MrpWeekDefinition::factory()->create();
        LogicalInventory::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'logical-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), LogicalInventoryExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        MrpWeekDefinition::factory()->create();
        LogicalInventory::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'logical-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), LogicalInventoryExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

}
