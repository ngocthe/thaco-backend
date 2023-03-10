<?php

namespace Tests\Unit\Services;

use App\Exports\MrpResultByMscExport;
use App\Exports\MrpResultByPartExport;
use App\Models\Admin;
use App\Models\MrpProductionPlanImport;
use App\Models\MrpResult;
use App\Models\MrpWeekDefinition;
use App\Models\Msc;
use App\Models\Part;
use App\Models\Plant;
use App\Models\ProductionPlan;
use App\Services\MrpResultService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class MrpResultTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MrpResultService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(MrpResult::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_part()
    {
        list($params) = $this->createMscData();
        $listItemQuery = MrpResult::query()
            ->selectRaw("
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(production_date SEPARATOR ',') as days
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->groupBy(['part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->getMrpResultsByPart();
        $this->assert_result($listItemService, $listItemQuery);
    }

    public function test_part_group_by_month()
    {
        list($params) = $this->createMscData();
        request()->merge(['group_by' => 'month']);

        $listItemQuery = MrpResult::query()
            ->selectRaw("
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(month_no SEPARATOR ',') as months
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->groupBy(['part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->getMrpResultsByPart();
        $this->assert_result($listItemService, $listItemQuery);
    }

    public function test_part_group_by_week()
    {
        list($params) = $this->createMscData();
        request()->merge(['group_by' => 'week']);

        $listItemQuery = MrpResult::query()
            ->selectRaw("
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(week_no SEPARATOR ',') as weeks
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->groupBy(['part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->getMrpResultsByPart();
        $this->assert_result($listItemService, $listItemQuery);
    }

    private function assert_result($listItemService, $listItemQuery)
    {
        $instanceModel = new MrpResult();
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

    public function test_part_has_not_import_id()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()
            ->sequence(fn($sequence) => [
                'mrp_or_status' => MrpProductionPlanImport::STATUS_RAN_MRP,
                'mrp_or_progress' => 100
            ])->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult['production_date']
        ])->create();
        request()->merge($params);

        $listItemQuery = MrpResult::query()
            ->selectRaw("
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(production_date SEPARATOR ',') as days
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $mrpResult['import_id'])
            ->groupBy(['part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->getMrpResultsByPart();

        $this->assert_result($listItemService, $listItemQuery);
    }

    public function test_part_has_search()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $part = Part::factory()->sequence(fn($sequence) => [
            'code' => $mrpResult['part_code']
        ])->create();
        $mrpWeekDefinition = MrpWeekDefinition::factory()->create();
        $params = [
            'import_id' => $mrpResult['import_id'],
            'msc_code' => $mrpResult['msc_code'],
            'vehicle_color_code' => $mrpResult['vehicle_color_code'],
            'part_code' => $mrpResult['part_code'],
            'part_color_code' => $mrpResult['part_color_code'],
            'plant_code' => $mrpResult['plant_code'],
            'year' => $mrpWeekDefinition['year'],
            'month' => $mrpWeekDefinition['month_no'],
            'part_group' => $part['group'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult['production_date']
        ])->create();
        request()->merge($params);

        $listItemQuery = MrpResult::query()
            ->selectRaw("
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(production_date SEPARATOR ',') as days
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->leftJoin('parts', function ($join) {
                $join->on('mrp_results.part_code', '=', 'parts.code');
            })
            ->where('group', '=', $params['part_group'])
            ->where('import_id', '=', $params['import_id'])
            ->where(['year' => $params['year'], 'month_no' => $params['month']])
            ->where('msc_code', '=', $params['msc_code'])
            ->where('vehicle_color_code', '=', $params['vehicle_color_code'])
            ->where('part_code', '=', $params['part_code'])
            ->where('part_color_code', '=', $params['part_color_code'])
            ->where('mrp_results.plant_code', '=', $params['plant_code'])
            ->groupBy(['part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->getMrpResultsByPart();

        $this->assert_result($listItemService, $listItemQuery);
    }

    public function test_msc_result()
    {
        list($params) = $this->createMscData();
        $listItemQuery = MrpResult::query()
            ->selectRaw("
                msc_code,
                vehicle_color_code,
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(production_date SEPARATOR ',') as days
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->groupBy(['msc_code', 'vehicle_color_code', 'part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('msc_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        $listItemService = $this->service->getMrpResultsByMSC();

        $this->assert_result($listItemService, $listItemQuery);
    }

    public function test_msc_result_group_month()
    {
        list($params) = $this->createMscData();
        request()->merge(['group_by' => 'month']);

        $listItemQuery = MrpResult::query()
            ->selectRaw("
                msc_code,
                vehicle_color_code,
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(month_no SEPARATOR ',') as months
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->groupBy(['msc_code', 'vehicle_color_code', 'part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('msc_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        $listItemService = $this->service->getMrpResultsByMSC();

        $this->assert_result($listItemService, $listItemQuery);
    }

    public function test_msc_result_group_week()
    {
        list($params) = $this->createMscData();
        request()->merge(['group_by' => 'week']);

        $listItemQuery = MrpResult::query()
            ->selectRaw("
                msc_code,
                vehicle_color_code,
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(week_no SEPARATOR ',') as weeks
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->groupBy(['msc_code', 'vehicle_color_code', 'part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('msc_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        $listItemService = $this->service->getMrpResultsByMSC();

        $this->assert_result($listItemService, $listItemQuery);
    }

    public function test_msc_result_has_search()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $part = Part::factory()->sequence(fn($sequence) => [
            'code' => $mrpResult['part_code']
        ])->create();
        $mrpWeekDefinition = MrpWeekDefinition::factory()->create();
        $params = [
            'import_id' => $mrpResult['import_id'],
            'msc_code' => $mrpResult['msc_code'],
            'vehicle_color_code' => $mrpResult['vehicle_color_code'],
            'part_code' => $mrpResult['part_code'],
            'part_color_code' => $mrpResult['part_color_code'],
            'plant_code' => $mrpResult['plant_code'],
            'year' => $mrpWeekDefinition['year'],
            'month' => $mrpWeekDefinition['month_no'],
            'part_group' => $part['group'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult['production_date']
        ])->create();
        request()->merge($params);

        $listItemQuery = MrpResult::query()
            ->selectRaw("
                 msc_code,
                vehicle_color_code,
                part_code,
                part_color_code,
                mrp_results.plant_code,
                GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities
            ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date')
            ->leftJoin('parts', function ($join) {
                $join->on('mrp_results.part_code', '=', 'parts.code');
            })
            ->where('group', '=', $params['part_group'])
            ->where('import_id', '=', $params['import_id'])
            ->where(['year' => $params['year'], 'month_no' => $params['month']])
            ->where('msc_code', '=', $params['msc_code'])
            ->where('vehicle_color_code', '=', $params['vehicle_color_code'])
            ->where('part_code', '=', $params['part_code'])
            ->where('part_color_code', '=', $params['part_color_code'])
            ->where('mrp_results.plant_code', '=', $params['plant_code'])
            ->groupBy(['msc_code', 'vehicle_color_code', 'part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('msc_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->getMrpResultsByMSC();

        $this->assert_result($listItemService, $listItemQuery);
    }

    private function createMscData()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'import_id' => $mrpResult['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult['production_date']
        ])->create();
        request()->merge($params);

        return [$params, $mrpResult];
    }

    public function test_msc_data()
    {
        list(, $mrpResult) = $this->createMscData();
        $listItemService = $this->service->getMrpResultsByMSC();
        $mscData = $this->service->getProductionPlanVolume($listItemService);
        $mscCode = array_keys($mscData)[0];
        $this->assertIsArray($mscData);
        $this->assertEquals($mrpResult->msc_code, $mscCode);
        $this->assertEquals([
            'production_date' => $mrpResult->production_date,
            'volume' => $mrpResult->production_volume
        ], $mscData[$mscCode][0]);
    }

    public function test_msc_data_group_by_week()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'import_id' => $mrpResult['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        $mrpWeekDefinition = MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult['production_date']
        ])->create();
        request()->merge($params);

        $groupBy = 'week';
        request()->merge(['group_by' => $groupBy]);

        $listItemService = $this->service->getMrpResultsByMSC();
        $mscData = $this->service->getProductionPlanVolume($listItemService, $groupBy);
        $mscCode = array_keys($mscData)[0];
        $this->assertIsArray($mscData);
        $this->assertEquals($mrpResult->msc_code, $mscCode);
        $this->assertEquals([
            'week_no' => $mrpWeekDefinition->week_no,
            'volume' => $mrpResult->production_volume
        ], $mscData[$mscCode][0]);
    }

    public function test_msc_data_group_by_month()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'import_id' => $mrpResult['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        $mrpWeekDefinition = MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult['production_date']
        ])->create();
        request()->merge($params);
        $groupBy = 'month';
        request()->merge(['group_by' => $groupBy]);

        $listItemService = $this->service->getMrpResultsByMSC();
        $mscData = $this->service->getProductionPlanVolume($listItemService, $groupBy);
        $mscCode = array_keys($mscData)[0];
        $this->assertIsArray($mscData);
        $this->assertEquals($mrpResult->msc_code, $mscCode);
        $this->assertEquals([
            'month_no' => $mrpWeekDefinition->month_no,
            'volume' => $mrpResult->production_volume
        ], $mscData[$mscCode][0]);
    }

    public function test_msc_data_group_by_day_same_msc_code()
    {
        $mscCodeInit = Str::random(7);
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'msc_code' => $mscCodeInit,
            'import_id' => $mrpProdPlanImport->getKey()
        ])->count(2)->create();
        $params = [
            'import_id' => $mrpResult[0]['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        $mrpWeekDefinition = MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult[0]['production_date']
        ])->create();
        request()->merge($params);

        $listItemService = $this->service->getMrpResultsByMSC();
        $mscData = $this->service->getProductionPlanVolume($listItemService);
        $mscCode = array_keys($mscData)[0];
        $volume = MrpResult::query()->sum('production_volume');
        $this->assertIsArray($mscData);
        $this->assertEquals($mscCodeInit, $mscCode);
        $this->assertEquals([
            'production_date' => $mrpWeekDefinition->date,
            'volume' => $volume
        ], $mscData[$mscCode][0]);
    }

    public function test_msc_data_group_by_week_same_msc_code()
    {
        $mscCodeInit = Str::random(7);
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'msc_code' => $mscCodeInit,
            'import_id' => $mrpProdPlanImport->getKey()
        ])->count(2)->create();
        $params = [
            'import_id' => $mrpResult[0]['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        $mrpWeekDefinition = MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult[0]['production_date']
        ])->create();
        request()->merge($params);
        $groupBy = 'week';
        request()->merge(['group_by' => $groupBy]);

        $listItemService = $this->service->getMrpResultsByMSC();
        $mscData = $this->service->getProductionPlanVolume($listItemService, $groupBy);
        $mscCode = array_keys($mscData)[0];
        $volume = MrpResult::query()->sum('production_volume');
        $this->assertIsArray($mscData);
        $this->assertEquals($mscCodeInit, $mscCode);
        $this->assertEquals([
            'week_no' => $mrpWeekDefinition->week_no,
            'volume' => $volume
        ], $mscData[$mscCode][0]);
    }

    public function test_msc_data_group_by_month_same_msc_code()
    {
        $mscCodeInit = Str::random(7);
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $mrpResult = MrpResult::factory()->sequence(fn($sequence) => [
            'msc_code' => $mscCodeInit,
            'import_id' => $mrpProdPlanImport->getKey()
        ])->count(2)->create();
        $params = [
            'import_id' => $mrpResult[0]['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        $mrpWeekDefinition = MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $mrpResult[0]['production_date']
        ])->create();
        request()->merge($params);
        $groupBy = 'month';
        request()->merge(['group_by' => $groupBy]);

        $listItemService = $this->service->getMrpResultsByMSC();
        $mscData = $this->service->getProductionPlanVolume($listItemService, $groupBy);
        $mscCode = array_keys($mscData)[0];
        $volume = MrpResult::query()->sum('production_volume');
        $this->assertIsArray($mscData);
        $this->assertEquals($mscCodeInit, $mscCode);
        $this->assertEquals([
            'month_no' => $mrpWeekDefinition->month_no,
            'volume' => $volume
        ], $mscData[$mscCode][0]);
    }

    public function test_columns()
    {
        $mrpResult = MrpResult::factory()->count(self::NUMBER_RECORD)->create();
        $mrpResultFillable = $mrpResult->first()->getFillable();
        $column = Arr::random($mrpResultFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $mrpResult->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $mrpResultQuery = MrpResult::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($mrpResultQuery, $result);
    }

    public function test_by_part_export()
    {
        MrpResult::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'mrp-result-parts';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), MrpResultByPartExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_by_part_export_type_pdf()
    {
        MrpResult::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'mrp-result-parts';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), MrpResultByPartExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_by_msc_export()
    {
        MrpResult::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'mrp-result';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), MrpResultByMscExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_by_msc_export_type_pdf()
    {
        MrpResult::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'mrp-result';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), MrpResultByMscExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
