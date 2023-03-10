<?php

namespace Tests\Unit\Services;

use App\Exports\BwhInventoryLogExport;
use App\Models\Admin;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\InTransitInventoryLog;
use App\Models\Remark;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Models\WarehouseLocation;
use App\Services\BwhInventoryLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class BwhInventoryLogTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;
    private $attrStore = [
        'contract_code',
        'invoice_code',
        'bill_of_lading_code',
        'container_code',
        'case_code',
        'part_code',
        'part_color_code',
        'box_type_code',
        'box_quantity',
        'part_quantity',
        'unit',
        'supplier_code',
        'container_received',
        'devanned_date',
        'stored_date',
        'warehouse_location_code',
        'warehouse_code',
        'shipped_date',
        'plant_code',
        'defect_id'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new BwhInventoryLogService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(BwhInventoryLog::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        BwhInventoryLog::factory()->count(self::NUMBER_RECORD)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(BwhInventoryLog::class, $this->service, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);

    }

    public function test_paginate_has_search()
    {

        $bwhInventoryLog = BwhInventoryLog::factory()->create();
        $params = [
            'contract_code' => $bwhInventoryLog['contract_code'],
            'invoice_code' => $bwhInventoryLog['invoice_code'],
            'bill_of_lading_code' => $bwhInventoryLog['bill_of_lading_code'],
            'container_code' =>  $bwhInventoryLog['container_code'],
            'case_code' =>  $bwhInventoryLog['case_code'],
            'box_type_code' => $bwhInventoryLog['box_type_code'],
            'supplier_code' => $bwhInventoryLog['supplier_code'],
            'devanned_date' => $bwhInventoryLog['devanned_date'],
            'container_received' => $bwhInventoryLog['container_received'],
            'stored_date' => $bwhInventoryLog['stored_date'],
            'warehouse_location_code' => $bwhInventoryLog['warehouse_location_code'],
            'warehouse_code' => $bwhInventoryLog['warehouse_code'],
            'shipped_date' => $bwhInventoryLog['shipped_date'],
            'part_code' =>  $bwhInventoryLog['part_code'],
            'part_color_code' => $bwhInventoryLog['part_color_code'],
            'plant_code' =>  $bwhInventoryLog['plant_code'],
            'updated_at' => $bwhInventoryLog['updated_at'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        $listItemQuery = BwhInventoryLog::query()
            ->where('contract_code', 'LIKE', '%' . $bwhInventoryLog['contract_code'] . '%')
            ->where('invoice_code', 'LIKE', '%' . $bwhInventoryLog['invoice_code'] . '%')
            ->where('bill_of_lading_code', 'LIKE', '%' . $bwhInventoryLog['bill_of_lading_code'] . '%')
            ->where('container_code', 'LIKE', '%' . $bwhInventoryLog['container_code'] . '%')
            ->where('case_code', 'LIKE', '%' . $bwhInventoryLog['case_code'] . '%')
            ->where('box_type_code', 'LIKE', '%' . $bwhInventoryLog['box_type_code'] . '%')
            ->where('supplier_code', 'LIKE', '%' . $bwhInventoryLog['supplier_code'] . '%')
            ->where('devanned_date', '=', $bwhInventoryLog['devanned_date'])
            ->where('container_received', '=', $bwhInventoryLog['container_received'])
            ->where('stored_date', '=', $bwhInventoryLog['stored_date'])
            ->where('warehouse_location_code', 'LIKE', '%' . $bwhInventoryLog['warehouse_location_code'] . '%')
            ->where('warehouse_code', 'LIKE', '%' . $bwhInventoryLog['warehouse_code'] . '%')
            ->where('shipped_date', '=', $bwhInventoryLog['shipped_date'])
            ->where('part_code', 'LIKE', '%' . $bwhInventoryLog['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $bwhInventoryLog['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $bwhInventoryLog['plant_code'] . '%')
            ->whereDate('updated_at', '=', $bwhInventoryLog['updated_at'])
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(BwhInventoryLog::class, $this->service, $params, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_columns()
    {
        $bwhInventories = BwhInventoryLog::factory()->count(self::NUMBER_RECORD)->create();
        $bwhInventoryFillable = $bwhInventories->first()->getFillable();
        $column = Arr::random($bwhInventoryFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $bwhInventories->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $bwhInventoryQuery = BwhInventoryLog::query()
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

    public function test_search_part()
    {
        $bwhInventories = BwhInventoryLog::factory()->sequence([
            'defect_id' => NULL,
            'requested' => 0
        ])->count(self::NUMBER_RECORD)->create();

        $code = $bwhInventories[rand(0, 19)]['part_code'];

        request()->merge([
            'code' => $code,
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $bwhInventoryQuery = BwhInventoryLog::query()
            ->select('part_code')
            ->where('part_code', 'LIKE', '%' . $params['code'] . '%')
            ->distinct()
            ->orderBy('part_code')
            ->limit($params['per_page'])
            ->pluck('part_code')
            ->toArray();

        $result = $this->service->searchPart();

        $this->assertArraySubset($bwhInventoryQuery, $result);
    }

    public function test_search_part_color()
    {
        $bwhInventories = BwhInventoryLog::factory()->sequence([
            'defect_id' => NULL,
            'requested' => 0
        ])->count(self::NUMBER_RECORD)->create();

        $index = rand(0, 19);
        $partCode = $bwhInventories[$index]['part_code'];
        $partColorCode = $bwhInventories[$index]['part_color_code'];

        request()->merge([
            'part_code' => $partCode,
            'code' => $partColorCode,
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $bwhInventoryQuery = BwhInventoryLog::query()
            ->select('part_color_code')
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['code'] . '%')
            ->distinct()
            ->orderBy('part_color_code')
            ->limit($params['per_page'])
            ->pluck('part_color_code')
            ->toArray();

        $result = $this->service->searchPartColor();

        $this->assertArraySubset($bwhInventoryQuery, $result);
    }

    public function test_show()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->withDeleted()->create();
        $bwhInventoryLogFound = $this->service->show($bwhInventoryLog->getKey());
        $bwhInventoryLogFound->setAttribute('container_received', $bwhInventoryLog->container_received->format('Y-m-d'));
        $bwhInventoryLogFound->setAttribute('devanned_date', $bwhInventoryLog->devanned_date->format('Y-m-d'));
        $bwhInventoryLogFound->setAttribute('stored_date', $bwhInventoryLog->stored_date->format('Y-m-d'));
        $bwhInventoryLogFound->setAttribute('shipped_date', $bwhInventoryLog->shipped_date->format('Y-m-d'));

        $this->assertNotNull($bwhInventoryLogFound);
        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogFound);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogFound->getAttributes(), $bwhInventoryLogFound->getFillable()));
        $this->assertTrue($bwhInventoryLog->is($bwhInventoryLogFound));
        $this->assertEquals($bwhInventoryLog->getAttributes(), $bwhInventoryLogFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->withDeleted()->create();
        $bwhInventoryLogId = $bwhInventoryLog->getKey();
        $bwhInventoryLog->delete();
        $bwhInventoryLogFoundWithTrash = $this->service->show($bwhInventoryLogId, [], [], [], true);
        $bwhInventoryLogFoundWithTrash->setAttribute('container_received', $bwhInventoryLogFoundWithTrash->container_received->format('Y-m-d'));
        $bwhInventoryLogFoundWithTrash->setAttribute('devanned_date', $bwhInventoryLogFoundWithTrash->devanned_date->format('Y-m-d'));
        $bwhInventoryLogFoundWithTrash->setAttribute('stored_date', $bwhInventoryLogFoundWithTrash->stored_date->format('Y-m-d'));
        $bwhInventoryLogFoundWithTrash->setAttribute('shipped_date', $bwhInventoryLogFoundWithTrash->shipped_date->format('Y-m-d'));

        $this->assertNotNull($bwhInventoryLogFoundWithTrash);
        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogFoundWithTrash);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogFoundWithTrash->getAttributes(), $bwhInventoryLogFoundWithTrash->getFillable()));
        $this->assertTrue($bwhInventoryLog->is($bwhInventoryLogFoundWithTrash));
        $this->assertEquals($bwhInventoryLog->getAttributes(), $bwhInventoryLogFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($bwhInventoryLog);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store_and_update_wh_inv_summaries()
    {
        $whInvSummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'warehouse_type' => WarehouseInventorySummary::TYPE_BWH
            ])
            ->create();
        $quantityInitial = $whInvSummary->quantity;
        $inTransitLog = InTransitInventoryLog::factory()
            ->sequence([
                'part_code' => $whInvSummary->part_code,
                'part_color_code' => $whInvSummary->part_color_code,
                'plant_code' => $whInvSummary->plant_code
            ])
            ->create();
        InTransitInventoryLog::factory()->sequence([
            'contract_code'=> $inTransitLog['contract_code'],
            'invoice_code'=> $inTransitLog['invoice_code'],
            'bill_of_lading_code'=> $inTransitLog['bill_of_lading_code'],
            'container_code'=> $inTransitLog['container_code'],
            'case_code'=> $inTransitLog['case_code'],
            'plant_code'=> $inTransitLog['plant_code']
        ])
            ->count(self::NUMBER_RECORD)
            ->create();
        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence([
                'part_code' => $inTransitLog->part_code,
                'part_color_code' => $inTransitLog->part_color_code,
                'warehouse_code' => $whInvSummary->warehouse_code,
                'warehouse_type' => $whInvSummary->warehouse_type,
                'plant_code' => $inTransitLog->plant_code,
                'contract_code' => $inTransitLog->contract_code,
                'invoice_code' => $inTransitLog->invoice_code,
                'bill_of_lading_code' => $inTransitLog->bill_of_lading_code,
                'container_code' => $inTransitLog->container_code,
                'case_code' => $inTransitLog->case_code,
                'defect_id' => null,
            ])
            ->make();
        $quantityIncrease = $inTransitLog->box_quantity * $inTransitLog->part_quantity;
        $data = Arr::only($bwhInventoryLog->getAttributes(), $this->attrStore);
        $data['container_received'] = (new \DateTime($data['container_received']))->format('Y-m-d');
        $data['devanned_date'] = (new \DateTime($data['devanned_date']))->format('Y-m-d');
        $data['stored_date'] = (new \DateTime($data['stored_date']))->format('Y-m-d');
        // update quantity warehouse_inventory_summaries exits
        $bwhInventoryLogCreated = $this->service->store($data);
        $quantityUpdated = $quantityInitial + $quantityIncrease;
        $whInvSummaryUpdated = WarehouseInventorySummary::find($whInvSummary->id);
        $totalBwhLogNotParent = BwhInventoryLog::query()->where('is_parent_case', 0)->count();
        $this->assertEquals(self::NUMBER_RECORD, $totalBwhLogNotParent);
        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogCreated);
        $this->assertEquals($quantityUpdated, $whInvSummaryUpdated->quantity);
        $this->assertArraySubset(Arr::only($bwhInventoryLogCreated->getAttributes(), $bwhInventoryLog->getFillable()), $bwhInventoryLogCreated->getAttributes());
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogCreated->getAttributes(), $bwhInventoryLogCreated->getFillable()));
    }

    public function test_store()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        InTransitInventoryLog::factory()->sequence([
            'contract_code'=> $inTransitLog['contract_code'],
            'invoice_code'=> $inTransitLog['invoice_code'],
            'bill_of_lading_code'=> $inTransitLog['bill_of_lading_code'],
            'container_code'=> $inTransitLog['container_code'],
            'case_code'=> $inTransitLog['case_code'],
            'plant_code'=> $inTransitLog['plant_code']
        ])
            ->count(self::NUMBER_RECORD)
            ->create();
        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence([
                'contract_code'=> $inTransitLog['contract_code'],
                'invoice_code'=> $inTransitLog['invoice_code'],
                'bill_of_lading_code'=> $inTransitLog['bill_of_lading_code'],
                'container_code'=> $inTransitLog['container_code'],
                'case_code'=> $inTransitLog['case_code'],
                'plant_code'=> $inTransitLog['plant_code']
            ])
            ->make();
        $data = Arr::only($bwhInventoryLog->getAttributes(), $this->attrStore);
        $data['container_received'] = (new \DateTime($data['container_received']))->format('Y-m-d');
        $data['devanned_date'] = (new \DateTime($data['devanned_date']))->format('Y-m-d');
        $data['stored_date'] = (new \DateTime($data['stored_date']))->format('Y-m-d');
        // Create new warehouse_inventory_summaries
        $bwhInventoryLogCreated = $this->service->store($data);
        $totalBwhLogNotParent = BwhInventoryLog::query()->where('is_parent_case', 0)->count();
        $this->assertEquals(self::NUMBER_RECORD, $totalBwhLogNotParent);
        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogCreated);
        $this->assertArraySubset(Arr::only($bwhInventoryLogCreated->getAttributes(), $bwhInventoryLog->getFillable()), $bwhInventoryLogCreated->getAttributes());
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogCreated->getAttributes(), $bwhInventoryLogCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        InTransitInventoryLog::factory()->sequence([
            'contract_code'=> $inTransitLog['contract_code'],
            'invoice_code'=> $inTransitLog['invoice_code'],
            'bill_of_lading_code'=> $inTransitLog['bill_of_lading_code'],
            'container_code'=> $inTransitLog['container_code'],
            'case_code'=> $inTransitLog['case_code'],
            'plant_code'=> $inTransitLog['plant_code']
        ])
            ->count(self::NUMBER_RECORD)
            ->create();
        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code']
            ])
            ->make();
        $remark = Remark::factory()->forModel($bwhInventoryLog)->make();
        $data = Arr::only($bwhInventoryLog->getAttributes(), $this->attrStore);
        $data['container_received'] = (new \DateTime($data['container_received']))->format('Y-m-d');
        $data['devanned_date'] = (new \DateTime($data['devanned_date']))->format('Y-m-d');
        $data['stored_date'] = (new \DateTime($data['stored_date']))->format('Y-m-d');
        request()->merge(['remark' => $remark->content]);
        $bwhInventoryLogCreated = $this->service->store($data);
        $remarkCreated = $bwhInventoryLogCreated->remarkable()->first();

        $totalBwhLogNotParent = BwhInventoryLog::query()->where('is_parent_case', 0)->count();
        $this->assertEquals(self::NUMBER_RECORD, $totalBwhLogNotParent);
        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($bwhInventoryLogCreated->getAttributes(), $bwhInventoryLog->getFillable()), $bwhInventoryLogCreated->getAttributes());
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogCreated->getAttributes(), $bwhInventoryLogCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        BwhOrderRequest::factory()
            ->sequence([
                'contract_code' => $inTransitLog->contract_code,
                'invoice_code' => $inTransitLog->invoice_code,
                'bill_of_lading_code' => $inTransitLog->bill_of_lading_code,
                'container_code' => $inTransitLog->container_code,
                'case_code' => $inTransitLog->case_code,
                'plant_code' => $inTransitLog->plant_code
            ])->create();
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'shipped_date' => NULL
            ])
            ->create();

        $bwhInventoryLogNew = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'shipped_date' => NULL
            ])
            ->make();

        WarehouseLocation::factory()->sequence(fn($sequence) => [
            'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
            'code' => $bwhInventoryLogNew['warehouse_location_code'],
            'plant_code' => $bwhInventoryLogNew['plant_code']
        ])->create();

        $bwhInventoryLogNewAttributes = $bwhInventoryLogNew->getAttributes();

        $attrUpdate = [
            'container_received',
            'devanned_date',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'defect_id'
        ];
        $data = Arr::add(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), 'remark', 'remark');

        $bwhInventoryLogUpdated = $this->service->update($bwhInventoryLogOrigin->getKey(), $data);
        $bwhInventoryLogUpdated[0]->setAttribute('container_received', $bwhInventoryLogUpdated[0]->container_received->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('devanned_date', $bwhInventoryLogUpdated[0]->devanned_date->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('stored_date', $bwhInventoryLogUpdated[0]->stored_date->format('Y-m-d'));
        $bwhInventoryLogUpdatedAttributes = $bwhInventoryLogUpdated[0]->getAttributes();

        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogUpdated[0]);
        $this->assertArraySubset(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), $bwhInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogUpdatedAttributes, $bwhInventoryLogUpdated[0]->getFillable()));
        $this->assertDatabaseHas('bwh_order_requests', Arr::only($bwhInventoryLogUpdatedAttributes, ['warehouse_code', 'warehouse_location_code']));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOrigin->getAttributes(), $bwhInventoryLogOrigin->getFillable()));
    }

    public function test_unable_update_when_shipped()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'defect_id' => 'X',
                'shipped_date' => NULL
            ])
            ->create();
        $bwhInventoryLogNew = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'defect_id' => 'Y',
                'plant_code' => $inTransitLog['plant_code']
            ])
            ->make();
        WarehouseLocation::factory()->sequence(fn($sequence) => [
            'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
            'code' => $bwhInventoryLogNew['warehouse_location_code'],
            'plant_code' => $bwhInventoryLogNew['plant_code']
        ])->create();
        $bwhInventoryLogNewAttributes = $bwhInventoryLogNew->getAttributes();

        $attrUpdate = [
            'container_received',
            'devanned_date',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'defect_id'
        ];
        $data = Arr::add(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), 'remark', 'remark');

        $result = $this->service->update($bwhInventoryLogOrigin->getKey(), $data);

        $this->assertIsArray($result);
        $this->assertArraySubset($result, [false, 'Unable to update defect status when the container has been shipped.']);
    }

    public function test_update_case_change_warehouse()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        BwhOrderRequest::factory()
            ->sequence([
                'contract_code' => $inTransitLog->contract_code,
                'invoice_code' => $inTransitLog->invoice_code,
                'bill_of_lading_code' => $inTransitLog->bill_of_lading_code,
                'container_code' => $inTransitLog->container_code,
                'case_code' => $inTransitLog->case_code,
                'plant_code' => $inTransitLog->plant_code
            ])->create();

        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'defect_id' => NULL,
                'shipped_date' => NULL
            ])
            ->create();

        $whSummaryInit = WarehouseInventorySummary::factory()->sequence([
            'warehouse_code' => $bwhInventoryLogOrigin['warehouse_code'],
            'plant_code' => $bwhInventoryLogOrigin['plant_code'],
            'part_code' => $bwhInventoryLogOrigin['part_code'],
            'part_color_code' => $bwhInventoryLogOrigin['part_color_code'],
            'warehouse_type' => WarehouseInventorySummary::TYPE_BWH
        ])->create();

        $bwhInventoryLogNew = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'defect_id' => NULL,
                'shipped_date' => NULL
            ])
            ->make();
        WarehouseLocation::factory()->sequence(fn($sequence) => [
            'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
            'code' => $bwhInventoryLogNew['warehouse_location_code'],
            'plant_code' => $bwhInventoryLogNew['plant_code']
        ])->create();
        $bwhInventoryLogNewAttributes = $bwhInventoryLogNew->getAttributes();

        $attrUpdate = [
            'container_received',
            'devanned_date',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'defect_id'
        ];
        $data = Arr::add(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), 'remark', 'remark');

        $bwhInventoryLogUpdated = $this->service->update($bwhInventoryLogOrigin->getKey(), $data);
        $bwhInventoryLogUpdated[0]->setAttribute('container_received', $bwhInventoryLogUpdated[0]->container_received->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('devanned_date', $bwhInventoryLogUpdated[0]->devanned_date->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('stored_date', $bwhInventoryLogUpdated[0]->stored_date->format('Y-m-d'));
        $bwhInventoryLogUpdatedAttributes = $bwhInventoryLogUpdated[0]->getAttributes();

        $whSummaryUpdated = WarehouseInventorySummary::query()
            ->where([
                'warehouse_code' => $bwhInventoryLogOrigin['warehouse_code'],
                'plant_code' => $bwhInventoryLogOrigin['plant_code'],
                'part_code' => $bwhInventoryLogOrigin['part_code'],
                'part_color_code' => $bwhInventoryLogOrigin['part_color_code'],
            ])
            ->first();

        $whSummaryNew = WarehouseInventorySummary::query()
            ->where([
                'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
                'plant_code' => $bwhInventoryLogOrigin['plant_code'],
                'part_code' => $bwhInventoryLogOrigin['part_code'],
                'part_color_code' => $bwhInventoryLogOrigin['part_color_code'],
            ])
            ->first();

        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogUpdated[0]);
        $this->assertArraySubset(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), $bwhInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogUpdatedAttributes, $bwhInventoryLogUpdated[0]->getFillable()));
        $this->assertDatabaseHas('bwh_order_requests', Arr::only($bwhInventoryLogUpdatedAttributes, ['warehouse_code', 'warehouse_location_code']));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOrigin->getAttributes(), $bwhInventoryLogOrigin->getFillable()));
        if ($whSummaryUpdated) {
            $this->assertEquals($whSummaryInit->quantity - ($bwhInventoryLogOrigin['box_quantity'] * $bwhInventoryLogOrigin['part_quantity']), $whSummaryUpdated->quantity);
        }
        if ($whSummaryNew) {
            $this->assertEquals($bwhInventoryLogOrigin['box_quantity'] * $bwhInventoryLogOrigin['part_quantity'], $whSummaryNew->quantity);
        }
    }

    public function test_update_case_not_warehouse_to_has_warehouse()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        BwhOrderRequest::factory()
            ->sequence([
                'contract_code' => $inTransitLog->contract_code,
                'invoice_code' => $inTransitLog->invoice_code,
                'bill_of_lading_code' => $inTransitLog->bill_of_lading_code,
                'container_code' => $inTransitLog->container_code,
                'case_code' => $inTransitLog->case_code,
                'plant_code' => $inTransitLog->plant_code
            ])->create();

        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'defect_id' => NULL,
                'warehouse_code' => NULL,
                'shipped_date' => NULL
            ])
            ->create();

        $bwhInventoryLogNew = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'defect_id' => NULL,
                'shipped_date' => NULL
            ])
            ->make();
        WarehouseLocation::factory()->sequence(fn($sequence) => [
            'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
            'code' => $bwhInventoryLogNew['warehouse_location_code'],
            'plant_code' => $bwhInventoryLogNew['plant_code']
        ])->create();
        $bwhInventoryLogNewAttributes = $bwhInventoryLogNew->getAttributes();

        $attrUpdate = [
            'container_received',
            'devanned_date',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'defect_id'
        ];
        $data = Arr::add(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), 'remark', 'remark');

        $bwhInventoryLogUpdated = $this->service->update($bwhInventoryLogOrigin->getKey(), $data);
        $bwhInventoryLogUpdated[0]->setAttribute('container_received', $bwhInventoryLogUpdated[0]->container_received->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('devanned_date', $bwhInventoryLogUpdated[0]->devanned_date->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('stored_date', $bwhInventoryLogUpdated[0]->stored_date->format('Y-m-d'));
        $bwhInventoryLogUpdatedAttributes = $bwhInventoryLogUpdated[0]->getAttributes();

        $whSummary = WarehouseInventorySummary::query()
            ->where([
                'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
                'plant_code' => $bwhInventoryLogOrigin['plant_code'],
                'part_code' => $bwhInventoryLogOrigin['part_code'],
                'part_color_code' => $bwhInventoryLogOrigin['part_color_code'],
            ])
            ->first();

        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogUpdated[0]);
        $this->assertArraySubset(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), $bwhInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogUpdatedAttributes, $bwhInventoryLogUpdated[0]->getFillable()));
        $this->assertDatabaseHas('bwh_order_requests', Arr::only($bwhInventoryLogUpdatedAttributes, ['warehouse_code', 'warehouse_location_code']));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOrigin->getAttributes(), $bwhInventoryLogOrigin->getFillable()));
        $this->assertEquals($bwhInventoryLogOrigin['box_quantity'] * $bwhInventoryLogOrigin['part_quantity'], $whSummary->quantity);
    }

    public function test_update_case_update_shipped()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        BwhOrderRequest::factory()
            ->sequence([
                'contract_code' => $inTransitLog->contract_code,
                'invoice_code' => $inTransitLog->invoice_code,
                'bill_of_lading_code' => $inTransitLog->bill_of_lading_code,
                'container_code' => $inTransitLog->container_code,
                'case_code' => $inTransitLog->case_code,
                'plant_code' => $inTransitLog->plant_code
            ])->create();

        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'defect_id' => NULL,
                'shipped_date' => NULL
            ])
            ->create();
        $whSummaryInit = WarehouseInventorySummary::factory()->sequence([
            'part_code' => $bwhInventoryLogOrigin['part_code'],
            'part_color_code' => $bwhInventoryLogOrigin['part_color_code'],
            'warehouse_code' => $bwhInventoryLogOrigin['warehouse_code'],
            'warehouse_type' => WarehouseInventorySummary::TYPE_BWH,
            'plant_code' => $bwhInventoryLogOrigin['plant_code'],
            'quantity' => rand(10000, 50000)
        ])->create();

        $bwhInventoryLogNew = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'defect_id' => NULL
            ])
            ->make();
        WarehouseLocation::factory()->sequence(fn($sequence) => [
            'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
            'code' => $bwhInventoryLogNew['warehouse_location_code'],
            'plant_code' => $bwhInventoryLogNew['plant_code']
        ])->create();
        $bwhInventoryLogNewAttributes = $bwhInventoryLogNew->getAttributes();

        $attrUpdate = [
            'container_received',
            'devanned_date',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'defect_id'
        ];
        $data = Arr::add(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), 'remark', 'remark');

        $bwhInventoryLogUpdated = $this->service->update($bwhInventoryLogOrigin->getKey(), $data);
        $bwhInventoryLogUpdated[0]->setAttribute('container_received', $bwhInventoryLogUpdated[0]->container_received->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('devanned_date', $bwhInventoryLogUpdated[0]->devanned_date->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('stored_date', $bwhInventoryLogUpdated[0]->stored_date->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('shipped_date', $bwhInventoryLogUpdated[0]->shipped_date->format('Y-m-d'));
        $bwhInventoryLogUpdatedAttributes = $bwhInventoryLogUpdated[0]->getAttributes();

        $whSummary = WarehouseInventorySummary::query()
            ->where([
                'warehouse_code' => $bwhInventoryLogOrigin['warehouse_code'],
                'plant_code' => $bwhInventoryLogOrigin['plant_code'],
                'part_code' => $bwhInventoryLogOrigin['part_code'],
                'part_color_code' => $bwhInventoryLogOrigin['part_color_code'],
            ])
            ->first();

        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogUpdated[0]);
        $this->assertArraySubset(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), $bwhInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogUpdatedAttributes, $bwhInventoryLogUpdated[0]->getFillable()));
        $this->assertDatabaseHas('bwh_order_requests', Arr::only($bwhInventoryLogUpdatedAttributes, ['warehouse_code', 'warehouse_location_code']));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOrigin->getAttributes(), $bwhInventoryLogOrigin->getFillable()));
        $this->assertEquals($whSummaryInit->quantity - ($bwhInventoryLogOrigin['box_quantity'] * $bwhInventoryLogOrigin['part_quantity']), $whSummary->quantity);
    }

    public function test_update_with_remark()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'shipped_date' => NULL
            ])
            ->create();

        $bwhInventoryLogNew = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'shipped_date' => NULL
            ])
            ->make();
        WarehouseLocation::factory()->sequence(fn($sequence) => [
            'warehouse_code' => $bwhInventoryLogNew['warehouse_code'],
            'code' => $bwhInventoryLogNew['warehouse_location_code'],
            'plant_code' => $bwhInventoryLogNew['plant_code']
        ])->create();

        $remark = Remark::factory()->forModel($bwhInventoryLogNew)->make();
        $bwhInventoryLogNewAttributes = $bwhInventoryLogNew->getAttributes();
        $attrUpdate = [
            'container_received',
            'devanned_date',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'defect_id'
        ];
        $data = Arr::add(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), 'remark', 'remark');

        request()->merge(['remark' => $remark->content]);
        $bwhInventoryLogUpdated = $this->service->update($bwhInventoryLogOrigin->getKey(), $data);
        $bwhInventoryLogUpdated[0]->setAttribute('container_received', $bwhInventoryLogUpdated[0]->container_received->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('devanned_date', $bwhInventoryLogUpdated[0]->devanned_date->format('Y-m-d'));
        $bwhInventoryLogUpdated[0]->setAttribute('stored_date', $bwhInventoryLogUpdated[0]->stored_date->format('Y-m-d'));
        $bwhInventoryLogUpdatedAttributes = $bwhInventoryLogUpdated[0]->getAttributes();
        $remarkCreated = $bwhInventoryLogUpdated[0]->remarkable()->first();

        $this->assertInstanceOf(BwhInventoryLog::class, $bwhInventoryLogUpdated[0]);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($bwhInventoryLogNewAttributes, $attrUpdate), $bwhInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogUpdatedAttributes, $bwhInventoryLogUpdated[0]->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOrigin->getAttributes(), $bwhInventoryLogOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_unable_destroy()
    {
        // unable destroy because has order request
        $inTransitLog = InTransitInventoryLog::factory()->create();
        $bwhInvLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'shipped_date' => NULL
            ])
            ->create();
        BwhOrderRequest::query()->firstOrCreate([
            'contract_code' => $bwhInvLogOrigin['contract_code'],
            'invoice_code' => $bwhInvLogOrigin['invoice_code'],
            'bill_of_lading_code' => $bwhInvLogOrigin['bill_of_lading_code'],
            'container_code' => $bwhInvLogOrigin['container_code'],
            'case_code' => $bwhInvLogOrigin['case_code'],
            'part_code' => Str::random(10),
            'part_color_code' => Str::random(2),
            'box_type_code' => Str::random(5),
            'status' => rand(1, 5),
            'plant_code' => $bwhInvLogOrigin['plant_code']
        ]);
        $result = $this->service->destroy($bwhInvLogOrigin->getKey());

        $this->assertFalse($result[0]);
    }

    public function test_destroy_soft_with_update_summary()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'shipped_date' => NULL
            ])
            ->create();

        WarehouseInventorySummary::factory()
            ->sequence([
                'warehouse_code' => $bwhInventoryLogOrigin['warehouse_code'],
                'plant_code' => $bwhInventoryLogOrigin['plant_code'],
                'part_code' => $bwhInventoryLogOrigin['part_code'],
                'part_color_code' => $bwhInventoryLogOrigin['part_color_code'],
            ])
            ->create();

        Warehouse::factory()->sequence([
            'code' => $bwhInventoryLogOrigin['warehouse_code']
        ])->create();

        request()->merge([
            'update_summary' => true,
        ]);

        $attributes = $bwhInventoryLogOrigin->getAttributes();

        $result = $this->service->destroy($bwhInventoryLogOrigin->getKey());

        $this->assertIsArray($result);
        $this->assertTrue($result[0]);
        $this->assertDatabaseHas('bwh_inventory_logs', $attributes);
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($bwhInventoryLogOrigin);
    }

    public function test_destroy_soft()
    {
        $inTransitLog = InTransitInventoryLog::factory()->create();
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()
            ->sequence([
                'contract_code' => $inTransitLog['contract_code'],
                'invoice_code' => $inTransitLog['invoice_code'],
                'bill_of_lading_code' => $inTransitLog['bill_of_lading_code'],
                'container_code' => $inTransitLog['container_code'],
                'case_code' => $inTransitLog['case_code'],
                'plant_code' => $inTransitLog['plant_code'],
                'shipped_date' => NULL
            ])
            ->create();
        $attributes = $bwhInventoryLogOrigin->getAttributes();

        $result = $this->service->destroy($bwhInventoryLogOrigin->getKey());

        $this->assertTrue($result[0]);
        $this->assertDatabaseHas('bwh_inventory_logs', $attributes);
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($bwhInventoryLogOrigin);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_export()
    {
        BwhInventoryLog::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'bwh-inventory-log';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), BwhInventoryLogExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        BwhInventoryLog::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'bwh-inventory-log';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), BwhInventoryLogExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
