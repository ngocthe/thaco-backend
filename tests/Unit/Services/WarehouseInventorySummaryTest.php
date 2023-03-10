<?php

namespace Tests\Unit\Services;

use App\Exports\WarehouseInventorySummaryExport;
use App\Exports\WarehouseInventorySummaryGroupByPartExport;
use App\Models\Admin;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Services\AuthService;
use App\Services\WarehouseInventorySummaryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WarehouseInventorySummaryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AuthService
     */
    private $whInventorySummaryService;

    public function setUp(): void
    {
        parent::setUp();
        $admin = Admin::factory()->create();
        Auth::login($admin);
        $this->whInventorySummaryService = new WarehouseInventorySummaryService();
    }

    public function test_model()
    {
        $this->assertEquals(WarehouseInventorySummary::class, $this->whInventorySummaryService->model(), 'Base Service model() does not return correct Model Instance');
    }

    /**
     * @return void
     */
    public function test_paginate()
    {
        WarehouseInventorySummary::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $whInventorySummary = WarehouseInventorySummary::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->whInventorySummaryService->paginate($params);

        $whInventorySummaryModel = new WarehouseInventorySummary();
        $fillables = $whInventorySummaryModel->getFillable();

        $dataWarehouseInventorySummary = $whInventorySummary->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataWarehouseInventorySummary as $key => $wh) {
            $dataWarehouseInventorySummary[$key] = Arr::only($wh, $fillables);
        }

        foreach ($dataResult as $key => $wh) {
            $dataResult[$key] = Arr::only($wh, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataWarehouseInventorySummary, $dataResult);

    }

    /**
     * @return void
     */
    public function test_paginate_has_search()
    {
        $date = now();
        $params = [
            'warehouse_code' => 'BWH',
            'part_code' => 'BWLS55100',
            'part_color_code' => '11',
            'plant_code' => 'TMAC',
            'updated_at' => $date,
            'page' => 1,
            'per_page' => 20
        ];

        WarehouseInventorySummary::factory()
            ->sequence([
                'warehouse_code' => $params['warehouse_code'],
                'part_code' => $params['part_code'],
                'part_color_code' => $params['part_color_code'],
                'plant_code' => $params['plant_code'],
                'updated_at' => $params['updated_at'],
            ])
            ->create();

        $whInventorySummary = WarehouseInventorySummary::query()
            ->where('warehouse_code', 'LIKE', '%' . $params['warehouse_code'] . '%')
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->whereDate('updated_at', '=', $params['updated_at'])
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        $result = $this->whInventorySummaryService->paginate($params);

        $whInventorySummaryModel = new WarehouseInventorySummary();
        $fillables = $whInventorySummaryModel->getFillable();
        $fillables[] = 'updated_at';
        $dataWarehouseInventorySummary = $whInventorySummary->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataWarehouseInventorySummary as $key => $wh) {
            $dataWarehouseInventorySummary[$key] = Arr::only($wh, $fillables);
        }

        foreach ($dataResult as $key => $wh) {
            $dataResult[$key] = Arr::only($wh, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataWarehouseInventorySummary, $dataResult);
    }

    public function test_export()
    {
        WarehouseInventorySummary::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'warehouse-inventory-summary';
        $dateFile = now()->format('dmY');
        $response = $this->whInventorySummaryService->export(request(), WarehouseInventorySummaryExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_part_export()
    {
        WarehouseInventorySummary::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'warehouse-inventory-summary-part';
        $dateFile = now()->format('dmY');
        $response = $this->whInventorySummaryService->export(request(), WarehouseInventorySummaryGroupByPartExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_show()
    {
        $whInventorySummary = WarehouseInventorySummary::factory()->withDeleted()->create();
        $whInventorySummaryFound = $this->whInventorySummaryService->show($whInventorySummary->getKey());

        $this->assertNotNull($whInventorySummaryFound);
        $this->assertInstanceOf(WarehouseInventorySummary::class, $whInventorySummaryFound);
        $this->assertTrue($whInventorySummary->is($whInventorySummaryFound));
        $this->assertEquals($whInventorySummary->getAttributes(), $whInventorySummaryFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $whInventorySummary = WarehouseInventorySummary::factory()->withDeleted()->create();
        $whInventorySummaryId = $whInventorySummary->getKey();
        $whInventorySummary->delete();
        $warehouseInventoryFoundWithTrash = $this->whInventorySummaryService->show($whInventorySummaryId, [], [], [], true);

        $this->assertNotNull($warehouseInventoryFoundWithTrash);
        $this->assertInstanceOf(WarehouseInventorySummary::class, $warehouseInventoryFoundWithTrash);
        $this->assertTrue($whInventorySummary->is($warehouseInventoryFoundWithTrash));
        $this->assertEquals($whInventorySummary->getAttributes(), $warehouseInventoryFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($whInventorySummary);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->whInventorySummaryService->show(1);
    }

    public function test_part()
    {
        $params = [
            'page' => 1,
            'per_page' => 20
        ];
        WarehouseInventorySummary::factory()->count(5)->create();
        $queryIvnSummary = WarehouseInventorySummary::query()
            ->select('warehouse_code', 'warehouse_type')
            ->distinct()
            ->orderBy('warehouse_type')
            ->get()
            ->toArray();
        $warehouseCodes = [];
        foreach ($queryIvnSummary as $row) {
            if ($row['warehouse_type'] == Warehouse::TYPE_PLANT_WH) {
                $warehouseCodes[Warehouse::PLANT_WAREHOUSE_CODE] = Warehouse::TYPE_PLANT_WH;
                break;
            } else {
                $warehouseCodes[$row['warehouse_code']] = $row['warehouse_type'];
            }
        }
        $whInventorySummaries = WarehouseInventorySummary::query()
            ->selectRaw("part_code, part_color_code, unit, GROUP_CONCAT(quantity SEPARATOR ',') as quantity, GROUP_CONCAT( warehouse_code SEPARATOR ',') as warehouse_codes")
            ->groupBy(['part_code', 'part_color_code', 'unit'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        $whInventorySummaryModel = new WarehouseInventorySummary();
        $fillables = $whInventorySummaryModel->getFillable();

        $dataWhInventorySummaries = $whInventorySummaries->toArray();

        $warehouseCodeService = $this->whInventorySummaryService->getWarehouseCodes();
        $whInventorySummaryService = $this->whInventorySummaryService->filterGroupByPart();
        $dataWhInventorySummaryService = $whInventorySummaryService->toArray()['data'];

        foreach ($dataWhInventorySummaries as $key => $wh) {
            $dataWhInventorySummaries[$key] = Arr::only($wh, $fillables);
        }

        foreach ($dataWhInventorySummaryService as $key => $wh) {
            $dataWhInventorySummaryService[$key] = Arr::only($wh, $fillables);
        }

        $this->assertEquals($dataWhInventorySummaries, $dataWhInventorySummaryService);
        $this->assertEquals($warehouseCodes, $warehouseCodeService);
    }
}
