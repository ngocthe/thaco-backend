<?php

namespace Tests\Unit\Services;

use App\Exports\ProductionPlanExport;
use App\Models\Admin;
use App\Models\MrpProductionPlanImport;
use App\Models\MrpWeekDefinition;
use App\Models\ProductionPlan;
use App\Services\MrpProductionPlanImportService;
use App\Services\ProductionPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProductionPlanTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new ProductionPlanService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(ProductionPlan::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        ProductionPlan::factory()->count(self::NUMBER_RECORD)->create();
        $listItemQuery = ProductionPlan::query()
            ->selectRaw("
                msc_code, vehicle_color_code, production_plans.plant_code,
                GROUP_CONCAT(plan_date SEPARATOR ',') as days,
                GROUP_CONCAT(volume SEPARATOR ',') as volumes
            ")->join('mrp_week_definitions', 'production_plans.plan_date', '=', 'mrp_week_definitions.date')
            ->groupBy(['msc_code', 'vehicle_color_code', 'production_plans.plant_code'])
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('msc_code')
            ->get();
        request()->merge($params);
        $listItemService = $this->service->filterProductionPlant();

        $instanceModel = new ProductionPlan();
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

    public function test_paginate_has_search()
    {
        $prodPlan = ProductionPlan::factory()->create();
        $mrpWeekDefinition = MrpWeekDefinition::factory()->create();
        $params = [
            'msc_code' => $prodPlan['msc_code'],
            'vehicle_color_code' => $prodPlan['vehicle_color_code'],
            'year' => $mrpWeekDefinition['year'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        ProductionPlan::factory()->count(self::NUMBER_RECORD)->create();
        $listItemQuery = ProductionPlan::query()
            ->selectRaw("
                msc_code, vehicle_color_code, production_plans.plant_code,
                GROUP_CONCAT(plan_date SEPARATOR ',') as days,
                GROUP_CONCAT(volume SEPARATOR ',') as volumes
            ")->join('mrp_week_definitions', 'production_plans.plan_date', '=', 'mrp_week_definitions.date')
            ->where('msc_code', 'LIKE', '%' . $params['msc_code'] . '%')
            ->where('vehicle_color_code', 'LIKE', '%' . $params['vehicle_color_code'] . '%')
            ->where('year', '=', $params['year'])
            ->groupBy(['msc_code', 'vehicle_color_code', 'production_plans.plant_code'])
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('msc_code')
            ->get();
        request()->merge($params);
        $listItemService = $this->service->filterProductionPlant();

        $instanceModel = new ProductionPlan();
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

    public function test_get_import_file()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();

        $params = [
            'mrp_or_status' => $mrpProdPlanImport['mrp_or_status'],
            'original_file_name' => $mrpProdPlanImport['original_file_name'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        $listItemQuery = MrpProductionPlanImport::query()
            ->select('id', 'original_file_name')
            ->where('mrp_or_status', '=', $params['mrp_or_status'])
            ->where('original_file_name', 'LIKE', '%' . $params['original_file_name'] . '%' )
            ->latest('id')
            ->limit($params['per_page'])
            ->get()
            ->toArray();

        request()->merge($params);
        $listItemService = (new MrpProductionPlanImportService())->getImportFiles();
        $this->assertEquals($listItemQuery, $listItemService);
    }

    public function test_columns()
    {
        $prodPlan = ProductionPlan::factory()->count(self::NUMBER_RECORD)->create();
        $prodPlanFillable = $prodPlan->first()->getFillable();
        $column = Arr::random($prodPlanFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $prodPlan->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $prodPlanQuery = ProductionPlan::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($prodPlanQuery, $result);
    }


    public function test_export()
    {
        ProductionPlan::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'production-plan';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), ProductionPlanExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        ProductionPlan::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'production-plan';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), ProductionPlanExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
