<?php

namespace Tests\Unit\Services;

use App\Exports\BwhInventoryLogExport;
use App\Exports\PartUsageResultExport;
use App\Models\Admin;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\InTransitInventoryLog;
use App\Models\PartUsageResult;
use App\Models\Remark;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Services\BwhInventoryLogService;
use App\Services\PartUsageResultService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class PartUsageResultTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;
    private $attrStore = [
        'used_date',
        'part_code',
        'part_color_code',
        'plant_code',
        'quantity'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new PartUsageResultService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(PartUsageResult::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        PartUsageResult::factory()->count(self::NUMBER_RECORD)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(PartUsageResult::class, $this->service, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);

    }

    public function test_paginate_has_search()
    {
        $partUsageResult = PartUsageResult::factory()->create();
        $params = [
            'used_date' => $partUsageResult['used_date'],
            'part_code' => $partUsageResult['part_code'],
            'part_color_code' => $partUsageResult['part_color_code'],
            'plant_code' => $partUsageResult['plant_code'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        $listItemQuery = PartUsageResult::query()
            ->whereDate('used_date', '=', $params['used_date'])
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(PartUsageResult::class, $this->service, $params, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_columns()
    {
        $partUsageResult = PartUsageResult::factory()->count(self::NUMBER_RECORD)->create();
        $partUsageResultFillable = $partUsageResult->first()->getFillable();
        $column = Arr::random($partUsageResultFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $partUsageResult->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $partUsageResultQuery = PartUsageResult::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($partUsageResultQuery, $result);
    }

    public function test_show()
    {
        $partUsageResult = PartUsageResult::factory()->withDeleted()->create();
        $partUsageResultFound = $this->service->show($partUsageResult->getKey());

        $this->assertNotNull($partUsageResultFound);
        $this->assertInstanceOf(PartUsageResult::class, $partUsageResultFound);
        $this->assertDatabaseHas('part_usage_results', Arr::only($partUsageResultFound->getAttributes(), $partUsageResultFound->getFillable()));
        $this->assertTrue($partUsageResult->is($partUsageResultFound));
        $this->assertEquals($partUsageResult->getAttributes(), $partUsageResultFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $partUsageResult = PartUsageResult::factory()->withDeleted()->create();
        $partUsageResultId = $partUsageResult->getKey();
        $partUsageResult->delete();
        $partUsageResultFoundWithTrash = $this->service->show($partUsageResultId, [], [], [], true);

        $this->assertNotNull($partUsageResultFoundWithTrash);
        $this->assertInstanceOf(PartUsageResult::class, $partUsageResultFoundWithTrash);
        $this->assertDatabaseHas('part_usage_results', Arr::only($partUsageResultFoundWithTrash->getAttributes(), $partUsageResultFoundWithTrash->getFillable()));
        $this->assertTrue($partUsageResult->is($partUsageResultFoundWithTrash));
        $this->assertEquals($partUsageResult->getAttributes(), $partUsageResultFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($partUsageResult);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store_has_not_wh_summary()
    {
        $partUsageResult = PartUsageResult::factory()->make();
        $data = Arr::only($partUsageResult->getAttributes(), $this->attrStore);
        $result = $this->service->store($data);
        $this->assertIsArray($result);
        $this->assertArraySubset($result, [false, "Number must not be greater than current summary"]);
    }

    public function test_store_with_wh_summary_quantity_less_than_part_usage_result_quantity()
    {
        $whSummaryQuantity = mt_rand(1, 50);
        $whSummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'quantity' => $whSummaryQuantity,
                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH
            ])->create();

        $partUsageResult = PartUsageResult::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $whSummary['part_code'],
                'part_color_code' => $whSummary['part_color_code'],
                'plant_code' => $whSummary['plant_code'],
                'quantity' => mt_rand($whSummaryQuantity + 1, 100)
            ])->make();

        $data = Arr::only($partUsageResult->getAttributes(), $this->attrStore);

        $result = $this->service->store($data);

        $this->assertIsArray($result);
        $this->assertTrue($whSummary['quantity'] < $partUsageResult['quantity']);
        $this->assertArraySubset($result, [false, "Number must not be greater than current summary"]);
    }

    public function test_store()
    {
        $whSummaryQuantity = mt_rand(50, 100);
        $whSummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'quantity' => $whSummaryQuantity,
                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH
            ])->create();

        $partUsageResult = PartUsageResult::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $whSummary['part_code'],
                'part_color_code' => $whSummary['part_color_code'],
                'plant_code' => $whSummary['plant_code'],
                'quantity' => mt_rand(1, $whSummaryQuantity - 10)
            ])->make();

        $data = Arr::only($partUsageResult->getAttributes(), $this->attrStore);

        $result = $this->service->store($data);
        $whSummaryAfter = WarehouseInventorySummary::find($whSummary->getKey());

        $this->assertTrue($whSummaryAfter['quantity'] == $whSummary['quantity'] - $partUsageResult['quantity']);
        $this->assertIsArray($result);
        $this->assertInstanceOf(PartUsageResult::class, $result[0]);
        $this->assertArraySubset(Arr::only($partUsageResult->getAttributes(), $partUsageResult->getFillable()), $partUsageResult->getAttributes());
        $this->assertDatabaseHas('part_usage_results', Arr::only($result[0]->getAttributes(), $result[0]->getFillable()));
    }

    public function test_store_with_remark()
    {
        $whSummaryQuantity = mt_rand(50, 100);
        $whSummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'quantity' => $whSummaryQuantity,
                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH
            ])->create();

        $partUsageResult = PartUsageResult::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $whSummary['part_code'],
                'part_color_code' => $whSummary['part_color_code'],
                'plant_code' => $whSummary['plant_code'],
                'quantity' => mt_rand(1, $whSummaryQuantity - 10)
            ])->make();
        $remark = Remark::factory()->forModel($partUsageResult)->make();
        $data = Arr::only($partUsageResult->getAttributes(), $this->attrStore);
        request()->merge(['remark' => $remark->content]);
        $result = $this->service->store($data);
        $remarkCreated = $result[0]->remarkable()->first();
        $whSummaryAfter = WarehouseInventorySummary::find($whSummary->getKey());

        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertTrue($whSummaryAfter['quantity'] == $whSummary['quantity'] - $partUsageResult['quantity']);
        $this->assertIsArray($result);
        $this->assertInstanceOf(PartUsageResult::class, $result[0]);
        $this->assertArraySubset(Arr::only($partUsageResult->getAttributes(), $partUsageResult->getFillable()), $partUsageResult->getAttributes());
        $this->assertDatabaseHas('part_usage_results', Arr::only($result[0]->getAttributes(), $result[0]->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $quantityOrigin = mt_rand(1, 100);
        $partUsageResultOrigin = PartUsageResult::factory()
            ->sequence(fn($sequence) => [
                'quantity' => $quantityOrigin
            ])->create();

        $partUsageResultNew = PartUsageResult::factory()
            ->sequence(fn($sequence) => [
                'quantity' => mt_rand(1, 100)
            ])->make();
        $partUsageResultAttributes = $partUsageResultNew->getAttributes();
        $attrUpdate = [
            'quantity',
        ];
        $data = Arr::add(Arr::only($partUsageResultAttributes, $attrUpdate), 'remark', 'remark');

        $partUsageResultUpdated = $this->service->update($partUsageResultOrigin->getKey(), $data);
        $partUsageResultUpdatedAttr = $partUsageResultUpdated->getAttributes();

        $this->assertInstanceOf(PartUsageResult::class, $partUsageResultUpdated);
        $this->assertArraySubset(Arr::only($partUsageResultUpdatedAttr, $attrUpdate), $partUsageResultUpdatedAttr);
        $this->assertDatabaseHas('part_usage_results', Arr::only($partUsageResultUpdatedAttr, $partUsageResultUpdated->getFillable()));
        $this->assertDatabaseMissing('part_usage_results', Arr::only($partUsageResultOrigin->getAttributes(), $partUsageResultOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $quantityOrigin = mt_rand(1, 100);
        $partUsageResultOrigin = PartUsageResult::factory()
            ->sequence(fn($sequence) => [
                'quantity' => $quantityOrigin
            ])->create();

        $partUsageResultNew = PartUsageResult::factory()
            ->sequence(fn($sequence) => [
                'quantity' => mt_rand(1, 100)
            ])->make();
        $partUsageResultAttributes = $partUsageResultNew->getAttributes();

        $remark = Remark::factory()->forModel($partUsageResultNew)->make();
        request()->merge(['remark' => $remark->content]);
        $attrUpdate = [
            'quantity',
        ];
        $data = Arr::add(Arr::only($partUsageResultAttributes, $attrUpdate), 'remark', 'remark');

        $partUsageResultUpdated = $this->service->update($partUsageResultOrigin->getKey(), $data);
        $partUsageResultUpdatedAttr = $partUsageResultUpdated->getAttributes();
        $remarkCreated = $partUsageResultUpdated->remarkable()->first();

        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertInstanceOf(PartUsageResult::class, $partUsageResultUpdated);
        $this->assertArraySubset(Arr::only($partUsageResultUpdatedAttr, $attrUpdate), $partUsageResultUpdatedAttr);
        $this->assertDatabaseHas('part_usage_results', Arr::only($partUsageResultUpdatedAttr, $partUsageResultUpdated->getFillable()));
        $this->assertDatabaseMissing('part_usage_results', Arr::only($partUsageResultOrigin->getAttributes(), $partUsageResultOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_soft()
    {
        $partUsageResult = PartUsageResult::factory()->create();
        $attributes = $partUsageResult->getAttributes();

        $result = $this->service->destroy($partUsageResult->getKey());
        $attributes['quantity'] = 0;
        $this->assertTrue($result);
        $this->assertDatabaseHas('part_usage_results', $attributes);
        $this->assertDatabaseMissing('part_usage_results', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($partUsageResult);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_export()
    {
        PartUsageResult::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'part-usage-result';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PartUsageResultExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        PartUsageResult::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'part-usage-result';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PartUsageResultExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
