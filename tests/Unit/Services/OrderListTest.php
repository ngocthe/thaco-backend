<?php

namespace Tests\Unit\Services;

use App\Constants\MRP;
use App\Exports\DeliveringListExport;
use App\Exports\DeliveringListPdfExport;
use App\Exports\OrderListPdfExport;
use App\Exports\OrderListsExport;
use App\Models\Admin;
use App\Models\MrpOrderCalendar;
use App\Models\MrpProductionPlanImport;
use App\Models\OrderList;
use App\Models\Part;
use App\Models\Remark;
use App\Services\OrderListService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderListTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;
    private $attrStore = [
        'contract_code',
        'part_code',
        'part_color_code',
        'part_group',
        'actual_quantity',
        'supplier_code',
        'import_id',
        'moq',
        'mrp_quantity',
        'plant_code',
        'remark'
    ];

    private $attrUpdate = [
        'actual_quantity',
        'remark'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new OrderListService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(OrderList::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        OrderList::factory()->count(self::NUMBER_RECORD)->create();

        $listItemQuery = OrderList::query()
            ->whereIn('status', [MRP::MRP_ORDER_LIST_STATUS_WAIT, MRP::MRP_ORDER_LIST_STATUS_DONE])
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        $listItemService = $this->service->paginateWithDefaultAttribute($params);

        $instanceModel = new OrderList();
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
        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'status' => $orderList['status'],
            'eta' => $orderList['eta'],
            'is_popup_release' => $orderList['is_popup_release'],
            'contract_code' => $orderList['contract_code'],
            'part_group' => $orderList['part_group'],
            'supplier_code' => $orderList['supplier_code'],
            'import_id' => $orderList['import_id'],
            'part_code' => $orderList['part_code'],
            'part_color_code' => $orderList['part_color_code'],
            'plant_code' => $orderList['plant_code'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        $listItemQuery = OrderList::query()
            ->where('status', '=', $params['status'])
            ->whereIn('status', [MRP::MRP_ORDER_LIST_STATUS_WAIT, MRP::MRP_ORDER_LIST_STATUS_DONE])
            ->where('eta', '=', $params['eta'])
            ->where('contract_code', '=', $params['contract_code'])
            ->where('part_group', '=', $params['part_group'])
            ->where('supplier_code', '=', $params['supplier_code'])
            ->where('import_id', '=', $params['import_id'])
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();

        $listItemService = $this->service->paginateWithDefaultAttribute($params);

        $instanceModel = new OrderList();
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

    public function test_paginate_not_param_is_popup_release()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'import_id' => $mrpProdPlanImport->getKey()
        ])->create();
        $params = [
            'status' => $orderList['status'],
            'eta' => $orderList['eta'],
            'contract_code' => $orderList['contract_code'],
            'part_group' => $orderList['part_group'],
            'supplier_code' => $orderList['supplier_code'],
            'import_id' => $orderList['import_id'],
            'part_code' => $orderList['part_code'],
            'part_color_code' => $orderList['part_color_code'],
            'plant_code' => $orderList['plant_code'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        $listItemQuery = OrderList::query()
            ->where('status', '=', $params['status'])
            ->where('eta', '=', $params['eta'])
            ->where('contract_code', 'LIKE', '%' . $params['contract_code'] . '%')
            ->where('part_group', 'LIKE', '%' . $params['part_group'] . '%')
            ->where('supplier_code', 'LIKE', '%' . $params['supplier_code'] . '%')
            ->where('import_id', '=', $params['import_id'])
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(OrderList::class, $this->service, $params, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_delivering()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $importId = $mrpProdPlanImport->getKey();
        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'import_id' => $importId
        ])->create();
        request()->merge(['import_id' => $importId]);
        $orderListService = $this->service->paginateWithDeliveringStatus();
        $this->assertInstanceOf(LengthAwarePaginator::class, $orderListService);
        $this->assertArraySubset($orderList->getAttributes(), $orderListService->items()[0]->getAttributes());
    }

    public function test_delivering_has_params()
    {
        $mrpProdPlanImport = MrpProductionPlanImport::factory()->create();
        $importId = $mrpProdPlanImport->getKey();
        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'import_id' => $importId
        ])->create();
        $params = [
            'status' => $orderList['status'],
            'eta' => $orderList['eta'],
            'contract_code' => $orderList['contract_code'],
            'part_group' => $orderList['part_group'],
            'supplier_code' => $orderList['supplier_code'],
            'import_id' => $orderList['import_id'],
            'part_code' => $orderList['part_code'],
            'part_color_code' => $orderList['part_color_code'],
            'plant_code' => $orderList['plant_code'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        request()->merge($params);
        request()->merge(['import_id' => $importId]);
        $orderListService = $this->service->paginateWithDeliveringStatus();
        $this->assertInstanceOf(LengthAwarePaginator::class, $orderListService);
        $this->assertArraySubset($orderList->getAttributes(), $orderListService->items()[0]->getAttributes());
    }

    public function test_delivering_not_param_import_id()
    {
        OrderList::factory()->sequence(fn($sequence) => [
            'import_id' => null
        ])->create();
        request()->merge(['import_id' => '-1']);
        $orderListService = $this->service->paginateWithDeliveringStatus();
        $this->assertInstanceOf(LengthAwarePaginator::class, $orderListService);
        $this->assertEmpty($orderListService->toArray()['data']);
    }

    public function test_columns()
    {
        $orderList = OrderList::factory()->count(self::NUMBER_RECORD)->create();
        $orderListFillable = $orderList->first()->getFillable();
        $column = Arr::random($orderListFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $orderList->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $orderListQuery = OrderList::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($orderListQuery, $result);
    }

    public function test_show()
    {
        $orderList = OrderList::factory()->withDeleted()->create();
        $orderListFound = $this->service->show($orderList->getKey());

        $this->assertNotNull($orderListFound);
        $this->assertInstanceOf(OrderList::class, $orderListFound);
        $this->assertDatabaseHas('order_lists', Arr::only($orderListFound->getAttributes(), $orderListFound->getFillable()));
        $this->assertTrue($orderList->is($orderListFound));
        $this->assertEquals($orderList->getAttributes(), $orderListFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $orderList = OrderList::factory()->withDeleted()->create();
        $orderListId = $orderList->getKey();
        $orderList->delete();
        $orderListFoundWithTrash = $this->service->show($orderListId, [], [], [], true);

        $this->assertNotNull($orderListFoundWithTrash);
        $this->assertInstanceOf(OrderList::class, $orderListFoundWithTrash);
        $this->assertDatabaseHas('order_lists', Arr::only($orderListFoundWithTrash->getAttributes(), $orderListFoundWithTrash->getFillable()));
        $this->assertTrue($orderList->is($orderListFoundWithTrash));
        $this->assertEquals($orderList->getAttributes(), $orderListFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($orderList);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->create();
        $orderList = OrderList::factory()
            ->sequence(fn($sequence) => [
                'contract_code' => $mrpOrderCalendar['contract_code']
            ])
            ->make();
        $data = $this->service->mergeDataStore(Arr::only($orderList->getAttributes(), $this->attrStore));
        $isValidEta = $this->service->validateGroupKeyWithEtaUnique($data);
        if ($isValidEta) {
            $orderListCreated = $this->service->store($data);

            $this->assertInstanceOf(OrderList::class, $orderListCreated);
            $this->assertArraySubset(Arr::only($orderListCreated->getAttributes(), $orderList->getFillable()), $orderListCreated->getAttributes());
            $this->assertDatabaseHas('order_lists', Arr::only($orderListCreated->getAttributes(), $orderListCreated->getFillable()));
        }
    }

    public function test_store_case_exist_order()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->create();
        $orderList = OrderList::factory()
            ->sequence(fn($sequence) => [
                'eta' => $mrpOrderCalendar['eta'],
                'contract_code' => $mrpOrderCalendar['contract_code']
            ])
            ->create();
        $data = $this->service->mergeDataStore(Arr::only($orderList->getAttributes(), $this->attrStore));
        $data['eta'] = $mrpOrderCalendar['eta']->format('Y-m-d');
        $isValidEta = $this->service->validateGroupKeyWithEtaUnique($data);
        $this->assertFalse($isValidEta);
    }

    public function test_store_with_remark()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->create();
        $orderList = OrderList::factory()
            ->sequence(fn($sequence) => [
                'contract_code' => $mrpOrderCalendar['contract_code']
            ])
            ->make();
        $remark = Remark::factory()->forModel($orderList)->make();
        request()->merge(['remark' => $remark->content]);
        $data = $this->service->mergeDataStore(Arr::only($orderList->getAttributes(), $this->attrStore));
        $isValidEta = $this->service->validateGroupKeyWithEtaUnique($data);
        if ($isValidEta) {
            $orderListCreated = $this->service->store($data);
            $remarkCreated = $orderListCreated->remarkable()->first();

            $this->assertInstanceOf(Remark::class, $remarkCreated);
            $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
            $this->assertInstanceOf(OrderList::class, $orderListCreated);
            $this->assertArraySubset(Arr::only($orderListCreated->getAttributes(), $orderList->getFillable()), $orderListCreated->getAttributes());
            $this->assertDatabaseHas('order_lists', Arr::only($orderListCreated->getAttributes(), $orderListCreated->getFillable()));
        }
    }

    public function test_update()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->create();
        $orderListOrigin = OrderList::factory()
            ->sequence(fn($sequence) => [
                'status' => MRP::MRP_ORDER_LIST_STATUS_WAIT,
                'contract_code' => $mrpOrderCalendar['contract_code']
            ])
            ->create();
        $orderListNew = OrderList::factory()
            ->sequence(fn($sequence) => [
                'contract_code' => $mrpOrderCalendar['contract_code']
            ])
            ->make();
        $orderListNewAttributes = $orderListNew->getAttributes();
        $data = Arr::add(Arr::only($orderListNewAttributes, $this->attrUpdate), 'remark', 'remark');
        $orderListUpdated = $this->service->update($orderListOrigin->getKey(), $data);
        $orderListUpdatedAttributes = $orderListUpdated->getAttributes();

        $this->assertInstanceOf(OrderList::class, $orderListUpdated);
        $this->assertArraySubset(Arr::only($orderListNewAttributes, $this->attrUpdate), $orderListUpdatedAttributes);
        $this->assertDatabaseHas('order_lists', Arr::only($orderListUpdatedAttributes, $orderListUpdated->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only($orderListOrigin->getAttributes(), $orderListOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->create();
        $orderListOrigin = OrderList::factory()
            ->sequence(fn($sequence) => [
                'status' => MRP::MRP_ORDER_LIST_STATUS_WAIT,
                'contract_code' => $mrpOrderCalendar['contract_code']
            ])
            ->create();
        $orderListNew = OrderList::factory()
            ->sequence(fn($sequence) => [
                'contract_code' => $mrpOrderCalendar['contract_code']
            ])
            ->make();
        $orderListNewAttributes = $orderListNew->getAttributes();
        $remark = Remark::factory()->forModel($orderListNew)->make();
        request()->merge(['remark' => $remark->content]);
        $data = Arr::add(Arr::only($orderListNewAttributes, $this->attrUpdate), 'remark', 'remark');
        $orderListUpdated = $this->service->update($orderListOrigin->getKey(), $data);
        $orderListUpdatedAttributes = $orderListUpdated->getAttributes();
        $remarkCreated = $orderListUpdated->remarkable()->first();

        $this->assertInstanceOf(OrderList::class, $orderListUpdated);
        $this->assertArraySubset(Arr::only($orderListNewAttributes, $this->attrUpdate), $orderListUpdatedAttributes);
        $this->assertDatabaseHas('order_lists', Arr::only($orderListUpdatedAttributes, $orderListUpdated->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only($orderListOrigin->getAttributes(), $orderListOrigin->getFillable()));
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_release()
    {
        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'status' => MRP::MRP_ORDER_CALENDAR_STATUS_WAIT
        ])->create();
        $mrpOrderCalendar = MrpOrderCalendar::factory()->sequence(fn($sequence) => [
            'status' => MRP::MRP_ORDER_CALENDAR_STATUS_WAIT,
            'contract_code' => $orderList['contract_code'],
            'part_group' => $orderList['part_group']
        ])->create();
        $attributes = [
            'contract_code' => $orderList['contract_code'],
            'supplier_code' => $orderList['supplier_code'],
            'plant_code' => $orderList['plant_code'],
            'part_group' => $orderList['part_group']
        ];
        $this->service->release($attributes);

        $orderListRelease = OrderList::find($orderList->getKey());
        $mrpOrderCalendarRelease = MrpOrderCalendar::find($mrpOrderCalendar->getKey());
        $this->assertEquals($orderListRelease->status, MRP::MRP_ORDER_LIST_STATUS_RELEASE);
        $this->assertEquals($mrpOrderCalendarRelease->status, MRP::MRP_ORDER_CALENDAR_STATUS_DONE);
    }

    public function test_destroy_soft()
    {
        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'status' => MRP::MRP_ORDER_LIST_STATUS_WAIT
        ])->create();
        $attributes = $orderList->getAttributes();

        $result = $this->service->destroy($orderList->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('order_lists', $attributes);
        $this->assertDatabaseMissing('order_lists', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($orderList);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_export()
    {
        OrderList::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'order-list';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), OrderListsExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        OrderList::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'order-list';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), OrderListPdfExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_delivering()
    {
        $part = Part::factory()->create();
        OrderList::factory()->sequence(fn($sequence) => [
            'part_code' => $part['code']
        ])->count(5)->create();
        $type = 'xlsx';
        $fileName = 'order-list';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), DeliveringListExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_delivering_type_pdf()
    {
        OrderList::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'order-list';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), DeliveringListPdfExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
