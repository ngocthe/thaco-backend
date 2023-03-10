<?php

namespace Tests\Unit\Services;

use App\Exports\BwhOrderRequestExport;
use App\Models\Admin;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\WarehouseInventorySummary;
use App\Services\BwhOrderRequestService;
use Faker\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class BwhOrderRequestTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new BwhOrderRequestService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(BwhOrderRequest::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        BwhOrderRequest::factory()->count(self::NUMBER_RECORD)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(BwhOrderRequest::class, $this->service, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);

    }

    public function test_paginate_has_search()
    {
        $bwhOrderRequest = BwhOrderRequest::factory()->create();
        $params = [
            'part_code' => $bwhOrderRequest['part_code'],
            'part_color_code' => $bwhOrderRequest['part_color_code'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        $listItemQuery = BwhOrderRequest::query()
            ->where('part_code', 'LIKE', '%' . $bwhOrderRequest['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $bwhOrderRequest['part_color_code'] . '%')
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(BwhOrderRequest::class, $this->service, $params, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_show()
    {
        $bwhOrderRequest = BwhOrderRequest::factory()->withDeleted()->create();
        $bwhOrderRequestFound = $this->service->show($bwhOrderRequest->getKey());

        $this->assertNotNull($bwhOrderRequestFound);
        $this->assertInstanceOf(BwhOrderRequest::class, $bwhOrderRequestFound);
        $this->assertDatabaseHas('bwh_order_requests', Arr::only($bwhOrderRequestFound->getAttributes(), $bwhOrderRequestFound->getFillable()));
        $this->assertTrue($bwhOrderRequest->is($bwhOrderRequestFound));
        $this->assertEquals($bwhOrderRequest->getAttributes(), $bwhOrderRequestFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $bwhOrderRequest = BwhOrderRequest::factory()->withDeleted()->create();
        $bwhOrderRequestId = $bwhOrderRequest->getKey();
        $bwhOrderRequest->delete();
        $bwhOrderRequestFoundWithTrash = $this->service->show($bwhOrderRequestId, [], [], [], true);

        $this->assertNotNull($bwhOrderRequestFoundWithTrash);
        $this->assertInstanceOf(BwhOrderRequest::class, $bwhOrderRequestFoundWithTrash);
        $this->assertDatabaseHas('bwh_order_requests', Arr::only($bwhOrderRequestFoundWithTrash->getAttributes(), $bwhOrderRequestFoundWithTrash->getFillable()));
        $this->assertTrue($bwhOrderRequest->is($bwhOrderRequestFoundWithTrash));
        $this->assertEquals($bwhOrderRequest->getAttributes(), $bwhOrderRequestFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($bwhOrderRequest);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store_not_order_request()
    {
        $bwhOrderRequest = BwhOrderRequest::factory()->make();
        $data = [
            'part_code' => $bwhOrderRequest['part_code'],
            'part_color_code' => $bwhOrderRequest['part_color_code'],
            'plant_code' => 'AAA'
        ];

        BwhInventoryLog::factory()
            ->sequence(array_merge($data, [
                'shipped_date' => NULL,
                'defect_id' => NULL
            ]))
            ->count(self::NUMBER_RECORD)
            ->create();

        $dataStore = Arr::only($bwhOrderRequest->getAttributes(), [
            'part_code',
            'part_color_code',
            'box_quantity',
            'plant_code'
        ]);

        $bwhOrderRequestCreated = $this->service->store($dataStore);
        $this->assertEquals($bwhOrderRequestCreated, 0);
    }

    public function test_store()
    {
        list($bwhOrderRequest, $data) = $this->createDataTest();
        $dataStore = Arr::only($bwhOrderRequest->getAttributes(), [
            'part_code',
            'part_color_code',
            'box_quantity',
            'plant_code'
        ]);

        $bwhOrderRequestCreated = $this->service->store($dataStore);
        $bwhInvLogUpdated = BwhInventoryLog::query()->where('requested', true)->count();
        $this->assertEquals($bwhOrderRequestCreated, $bwhInvLogUpdated);
        $this->assertDatabaseHas('bwh_order_requests', $data);
    }

    private function createDataTest(): array
    {
        $bwhOrderRequest = BwhOrderRequest::factory()->make();
        $data = [
            'part_code' => $bwhOrderRequest['part_code'],
            'part_color_code' => $bwhOrderRequest['part_color_code'],
            'plant_code' => $bwhOrderRequest['plant_code']
        ];

        $dataCaseOne = [
            'contract_code' => Str::random(5),
            'invoice_code' => Str::random(5),
            'bill_of_lading_code' => Str::random(5),
            'container_code' => Str::random(5),
            'case_code' => Str::random(5),
            'shipped_date' => NULL,
            'defect_id' => NULL
        ];

        BwhInventoryLog::factory()
            ->sequence(array_merge($data, $dataCaseOne))
            ->create();
        BwhInventoryLog::factory()
            ->sequence($dataCaseOne)
            ->count(4)
            ->create();

        $dataCaseTwo = [
            'contract_code' => Str::random(5),
            'invoice_code' => Str::random(5),
            'bill_of_lading_code' => Str::random(5),
            'container_code' => Str::random(5),
            'case_code' => Str::random(5),
            'shipped_date' => NULL,
            'defect_id' => NULL
        ];

        BwhInventoryLog::factory()
            ->sequence(array_merge($data, $dataCaseTwo))
            ->create();
        BwhInventoryLog::factory()
            ->sequence($dataCaseTwo)
            ->count(4)
            ->create();

        return [$bwhOrderRequest, $data];

    }

    public function test_confirm_bwh_order_request_case_defect_id_null()
    {
        $faker = Factory::create();
        list($bwhOrderRequest) = $this->createDataTest();

        $dataStore = Arr::only($bwhOrderRequest->getAttributes(), [
            'part_code',
            'part_color_code',
            'box_quantity',
            'plant_code'
        ]);

        $dataConfirm = [
            'received_date' => date('Y-m-d'),
            'warehouse_code' => $bwhOrderRequest['warehouse_code'],
            'warehouse_location_code' => $bwhOrderRequest['warehouse_location_code'],
            'remark' => $faker->text(100),
        ];

        request()->merge($dataConfirm);
        $this->service->store($dataStore);
        $bwhOrder = BwhOrderRequest::query()->first();
        $bwhOrderConfirm = $this->service->confirmBwhOrderRequest($bwhOrder->id, $dataConfirm);
        $this->assertSoftDeleted($bwhOrderConfirm[0]);
        $remark = array_pop($dataConfirm);
        $this->assertDatabaseHas('warehouse_inventory_summaries', [
            'warehouse_code' => $bwhOrderRequest['warehouse_code'],
            'warehouse_type' => WarehouseInventorySummary::TYPE_UPKWH
        ]);
        $this->assertDatabaseHas('remarks', ['content' => $remark]);
        $this->assertDatabaseHas('upkwh_inventory_logs',  [
            'received_date' => $dataConfirm['received_date'],
            'warehouse_code' => $dataConfirm['warehouse_code'],
        ]);
    }

    public function test_confirm_bwh_order_request()
    {
        $faker = Factory::create();
        list($bwhOrderRequest) = $this->createDataTest();
        $dataStore = Arr::only($bwhOrderRequest->getAttributes(), [
            'part_code',
            'part_color_code',
            'box_quantity',
            'plant_code'
        ]);

        $dataConfirm = [
            'received_date' => date('Y-m-d'),
            'warehouse_code' => $bwhOrderRequest['warehouse_code'],
            'warehouse_location_code' => $bwhOrderRequest['warehouse_location_code'],
            'defect_id' => Arr::random(['W', 'D', 'X', 'S']),
            'remark' => $faker->text(100),
        ];

        request()->merge($dataConfirm);
        $this->service->store($dataStore);
        $bwhOrder = BwhOrderRequest::query()->first();
        $bwhOrderConfirm = $this->service->confirmBwhOrderRequest($bwhOrder->id, $dataConfirm);
        $this->assertSoftDeleted($bwhOrderConfirm[0]);
        $remark = array_pop($dataConfirm);
        $this->assertDatabaseHas('remarks', ['content' => $remark]);
        $this->assertDatabaseHas('upkwh_inventory_logs',  [
            'received_date' => $dataConfirm['received_date'],
            'warehouse_code' => $dataConfirm['warehouse_code'],
            'defect_id' => $dataConfirm['defect_id'],
        ]);
    }

    public function test_columns()
    {
        $bwhOrderRequest = BwhOrderRequest::factory()->count(self::NUMBER_RECORD)->create();
        $bwhOrderRequestFillable = $bwhOrderRequest->first()->getFillable();
        $column = Arr::random($bwhOrderRequestFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $bwhOrderRequest->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $bwhOrderRequestQuery = BwhOrderRequest::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($bwhOrderRequestQuery, $result);
    }


    public function test_export()
    {
        BwhOrderRequest::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'bwh_order_request';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), BwhOrderRequestExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        BwhOrderRequest::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'bwh_order_request';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), BwhOrderRequestExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

}
