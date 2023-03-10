<?php

namespace Tests\Unit\Services;

use App\Exports\ShortagePartExport;
use App\Models\Admin;
use App\Models\MrpProductionPlanImport;
use App\Models\MrpWeekDefinition;
use App\Models\Part;
use App\Models\Remark;
use App\Models\ShortagePart;
use App\Services\ShortagePartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ShortagePartTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;
    private $attrStore = [
        'part_code', 'part_color_code', 'plan_date', 'plant_code', 'import_id'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new ShortagePartService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(ShortagePart::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_get_remark()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $shortagePart = ShortagePart::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'import_id' => $shortagePart['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $shortagePart['plan_date']
        ])->create();

        $remark = Remark::factory()->sequence(fn($sequence) => [
            'modelable_type' => 'App\Models\ShortagePart',
            'modelable_id' => $shortagePart->getKey()
        ])->create();
        request()->merge($params);
        $listItemService = $this->service->filterShortagePart();
        $getRemark = $this->service->getRemarks($listItemService);
        $key = implode('-', [$shortagePart->part_code, $shortagePart->part_color_code, $shortagePart->plant_code, $shortagePart->import_id]);
        $res = $getRemark[$key][$shortagePart->plan_date][0];
        $this->assertIsArray($getRemark);
        $this->assertArraySubset(Arr::only($res, ['id', 'content']), $remark->toArray());
    }

    public function test_paginate()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $shortagePart = ShortagePart::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'import_id' => $shortagePart['import_id'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        MrpWeekDefinition::factory()->sequence(fn($sequence) => [
            'date' => $shortagePart['plan_date']
        ])->create();

        $listItemQuery = ShortagePart::query()
            ->selectRaw("
                part_code,
                part_color_code,
                import_id,
                shortage_parts.plant_code,
                GROUP_CONCAT(quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(plan_date SEPARATOR ',') as days
            ")->join('mrp_week_definitions', 'shortage_parts.plan_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->groupBy(['part_code', 'part_color_code', 'shortage_parts.plant_code', 'import_id'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->filterShortagePart();

        $instanceModel = new ShortagePart();
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
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $shortagePart = ShortagePart::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $part = Part::factory()->sequence(fn($sequence) => [
            'code' => $shortagePart['part_code']
        ])->create();
        $mrpWeekDefinition = MrpWeekDefinition::factory()->create();
        $params = [
            'import_id' => $shortagePart['import_id'],
            'part_code' => $shortagePart['part_code'],
            'part_color_code' => $shortagePart['part_color_code'],
            'year' => $mrpWeekDefinition['year'],
            'part_group' => $part['group'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        $listItemQuery = ShortagePart::query()
            ->selectRaw("
                part_code,
                part_color_code,
                import_id,
                shortage_parts.plant_code,
                GROUP_CONCAT(quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(plan_date SEPARATOR ',') as days
            ")->join('mrp_week_definitions', 'shortage_parts.plan_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', '=', $params['import_id'])
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('year', '=', $params['year'])
            ->groupBy(['part_code', 'part_color_code', 'shortage_parts.plant_code', 'import_id'])
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->offset(0)
            ->get();
        request()->merge($params);
        $listItemService = $this->service->filterShortagePart();

        $instanceModel = new ShortagePart();
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

    public function test_columns()
    {
        $shortagePart = ShortagePart::factory()->count(self::NUMBER_RECORD)->create();
        $shortagePartFillable = $shortagePart->first()->getFillable();
        $column = Arr::random($shortagePartFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $shortagePart->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $shortagePartQuery = ShortagePart::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($shortagePartQuery, $result);
    }

    public function test_add_remark()
    {
        $shortagePart = ShortagePart::factory()->create();
        $remark = Remark::factory()->forModel($shortagePart)->make();
        request()->merge(['remark' => $remark->content]);
        $data = Arr::only($shortagePart->getAttributes(), $this->attrStore);
        $this->service->addRemark(
            $data['part_code'],
            $data['part_color_code'],
            $data['plan_date'],
            $data['plant_code'],
            $data['import_id']
        );

        $remarkCreated = Remark::query()
            ->where('modelable_type', '=', 'App\Models\ShortagePart')
            ->where('modelable_id', '=', $shortagePart->getKey())
            ->first();
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertArraySubset($remark->toArray(), $remarkCreated->toArray());
    }

    public function test_export()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        ShortagePart::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->count(5)->create();
        $type = 'xlsx';
        $fileName = 'shortage-parts';
        $dateFile = now()->format('dmY');
        $response = $this->service->export(request(), ShortagePartExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        ShortagePart::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'shortage-parts';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), ShortagePartExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
