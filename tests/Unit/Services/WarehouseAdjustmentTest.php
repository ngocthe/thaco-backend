<?php

namespace Tests\Unit\Services;

use App\Exports\WarehouseSummaryAdjustmentExport;
use App\Models\Admin;
use App\Models\Remark;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Models\WarehouseSummaryAdjustment;
use App\Services\WarehouseSummaryAdjustmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WarehouseAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new WarehouseSummaryAdjustmentService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(WarehouseSummaryAdjustment::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => 20
        ];
        WarehouseSummaryAdjustment::factory()->count(20)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(WarehouseSummaryAdjustment::class, $this->service, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);

    }

    public function test_paginate_has_search()
    {
        $whAdjustment = WarehouseSummaryAdjustment::factory()->create();
        $params = [
            'warehouse_code' => $whAdjustment['contract_code'],
            'part_code' => $whAdjustment['part_code'],
            'plant_code' => $whAdjustment['plant_code'],
            'page' => 1,
            'per_page' => 20
        ];

        $listItemQuery = WarehouseSummaryAdjustment::query()
            ->where('warehouse_code', 'LIKE', '%' . $whAdjustment['contract_code'] . '%')
            ->where('part_code', 'LIKE', '%' . $whAdjustment['invoice_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $whAdjustment['bill_of_lading_code'] . '%')
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(WarehouseSummaryAdjustment::class, $this->service, $params, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_show()
    {
        $whAdjustment = WarehouseSummaryAdjustment::factory()->withDeleted()->create();
        $whAdjustmentFound = $this->service->show($whAdjustment->getKey());

        $this->assertNotNull($whAdjustmentFound);
        $this->assertInstanceOf(WarehouseSummaryAdjustment::class, $whAdjustmentFound);
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($whAdjustmentFound->getAttributes(), $whAdjustmentFound->getFillable()));
        $this->assertTrue($whAdjustmentFound->is($whAdjustmentFound));
        $this->assertEquals($whAdjustmentFound->getAttributes(), $whAdjustmentFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $whAdjustment = WarehouseSummaryAdjustment::factory()->withDeleted()->create();
        $whAdjustmentId = $whAdjustment->getKey();
        $whAdjustment->delete();
        $whAdjustmentFoundWithTrash = $this->service->show($whAdjustmentId, [], [], [], true);

        $this->assertNotNull($whAdjustmentFoundWithTrash);
        $this->assertInstanceOf(WarehouseSummaryAdjustment::class, $whAdjustmentFoundWithTrash);
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($whAdjustmentFoundWithTrash->getAttributes(), $whAdjustmentFoundWithTrash->getFillable()));
        $this->assertTrue($whAdjustment->is($whAdjustmentFoundWithTrash));
        $this->assertEquals($whAdjustment->getAttributes(), $whAdjustmentFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($whAdjustment);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $whAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $data = Arr::only($whAdjustment->getAttributes(), [
            'warehouse_code',
            'part_code',
            'part_color_code',
            'old_quantity',
            'new_quantity',
            'adjustment_quantity',
            'plant_code'
        ]);

        Warehouse::factory()->sequence(fn($sequence) => [
            'code' => $whAdjustment['warehouse_code']
        ])->create();

        $whAdjustmentCreated = $this->service->store($data);
        $whAdjustment['old_quantity'] = $whAdjustmentCreated[0]['old_quantity'];
        $whAdjustment['new_quantity'] = $whAdjustmentCreated[0]['new_quantity'];

        $this->assertInstanceOf(WarehouseSummaryAdjustment::class, $whAdjustmentCreated[0]);
        $this->assertArraySubset(Arr::only($whAdjustment->getAttributes(), $whAdjustment->getFillable()), $whAdjustmentCreated[0]->getAttributes());
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($whAdjustmentCreated[0]->getAttributes(), [
            'part_code',
            'part_color_code',
            'warehouse_code',
            'plant_code'
        ]));
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($whAdjustmentCreated[0]->getAttributes(), $whAdjustmentCreated[0]->getFillable()));
    }

    public function test_store_with_wh_summary_exits()
    {
        $whAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $data = Arr::only($whAdjustment->getAttributes(), [
            'warehouse_code',
            'part_code',
            'part_color_code',
            'old_quantity',
            'new_quantity',
            'adjustment_quantity',
            'plant_code'
        ]);
        $whSummary = WarehouseInventorySummary::factory()->sequence([
            'warehouse_code' => $whAdjustment['warehouse_code'],
            'plant_code' => $whAdjustment['plant_code'],
            'part_code' => $whAdjustment['part_code'],
            'part_color_code' => $whAdjustment['part_color_code']
        ])->create();
        Warehouse::factory()->sequence(fn($sequence) => [
            'code' => $whAdjustment['warehouse_code']
        ])->create();

        $whAdjustmentCreated = $this->service->store($data);
        $whAdjustment['old_quantity'] = $whSummary['quantity'];
        $whAdjustment['new_quantity'] = $whSummary['quantity'] + $whAdjustment['adjustment_quantity'];

        $this->assertInstanceOf(WarehouseSummaryAdjustment::class, $whAdjustmentCreated[0]);
        $this->assertArraySubset(Arr::only($whAdjustment->getAttributes(), $whAdjustment->getFillable()), $whAdjustmentCreated[0]->getAttributes());
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($whAdjustmentCreated[0]->getAttributes(), $whAdjustmentCreated[0]->getFillable()));
    }

    public function test_store_with_remark()
    {
        $whAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $remark = Remark::factory()->forModel($whAdjustment)->make();
        $data = Arr::only($whAdjustment->getAttributes(), [
            'warehouse_code',
            'part_code',
            'part_color_code',
            'old_quantity',
            'new_quantity',
            'adjustment_quantity',
            'plant_code'
        ]);

        Warehouse::factory()->sequence(fn($sequence) => [
            'code' => $whAdjustment['warehouse_code']
        ])->create();

        request()->merge(['remark' => $remark->content]);
        $whAdjustmentCreated = $this->service->store($data);
        $whAdjustment['old_quantity'] = $whAdjustmentCreated[0]['old_quantity'];
        $whAdjustment['new_quantity'] = $whAdjustmentCreated[0]['new_quantity'];
        $remarkCreated = $whAdjustmentCreated[0]->remarkable()->first();

        $this->assertInstanceOf(WarehouseSummaryAdjustment::class, $whAdjustmentCreated[0]);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($whAdjustment->getAttributes(), $whAdjustment->getFillable()), $whAdjustmentCreated[0]->getAttributes());
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($whAdjustmentCreated[0]->getAttributes(), $whAdjustmentCreated[0]->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_export()
    {
        WarehouseSummaryAdjustment::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'warehouse-summary-adjustment';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), WarehouseSummaryAdjustmentExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        WarehouseSummaryAdjustment::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'warehouse-summary-adjustment';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), WarehouseSummaryAdjustmentExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
