<?php

namespace Tests\Unit\Services;

use App\Exports\PlantExport;
use App\Models\Admin;
use App\Models\BoxType;
use App\Models\DefectInventory;
use App\Models\PlantInventoryLog;
use App\Models\Remark;
use App\Models\UpkwhInventoryLog;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Models\WarehouseSummaryAdjustment;
use App\Services\PlantInventoryLogService;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlantInventoryLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new PlantInventoryLogService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(PlantInventoryLog::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        PlantInventoryLog::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $plantInventoryLogs = PlantInventoryLog::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $plantInventoryLogModel = new PlantInventoryLog();
        $fillables = $plantInventoryLogModel->getFillable();

        $dataPlantInventoryLog = $plantInventoryLogs->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataPlantInventoryLog as $key => $plantInventoryLog) {
            $dataPlantInventoryLog[$key] = Arr::only($plantInventoryLog, $fillables);
        }

        foreach ($dataResult as $key => $plantInventoryLog) {
            $dataResult[$key] = Arr::only($plantInventoryLog, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataPlantInventoryLog, $dataResult);
    }

    public function test_paginate_has_search()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();

        $params = [
            'received_date' => $plantInventoryLogAttributes['received_date'],
            'warehouse_code' => $this->escapeLike($plantInventoryLogAttributes['warehouse_code']),
            'part_code' => $this->escapeLike($plantInventoryLogAttributes['part_code']),
            'part_color_code' => $this->escapeLike($plantInventoryLogAttributes['part_color_code']),
            'plant_code' => $this->escapeLike($plantInventoryLogAttributes['plant_code']),
            'defect_id' => Arr::random([true, false]),
            'updated_at' => Carbon::parse($plantInventoryLogAttributes['updated_at'])->format('Y-m-d'),
            'page' => 1,
            'per_page' => 20
        ];

        $query =  PlantInventoryLog::query()
            ->where('received_date', $params['received_date'])
            ->where('warehouse_code', 'LIKE', '%' . $params['warehouse_code'] . '%')
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->whereDate('updated_at', '=', $params['updated_at']);

        if ($params['defect_id']) {
            $query->whereNotNull('defect_id');
        } else {
            $query->whereNull('defect_id');
        }

        $plantInventoryLogs = $query->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $plantInventoryLogModel = new PlantInventoryLog();
        $fillables = $plantInventoryLogModel->getFillable();
        $fillables[] = 'updated_at';

        $dataPlantInventoryLog = $plantInventoryLogs->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataPlantInventoryLog as $key => $plantInventoryLog) {
            $dataPlantInventoryLog[$key] = Arr::only($plantInventoryLog, $fillables);
        }

        foreach ($dataResult as $key => $plantInventoryLog) {
            $dataResult[$key] = Arr::only($plantInventoryLog, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataPlantInventoryLog, $dataResult);
    }

    private function escapeLike(string $value, string $char = '\\'): string
    {
        return str_replace(
            [$char, '%', '_'],
            [$char . $char, $char . '%', $char . '_'],
            $value
        );
    }

    public function test_show()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->withDeleted()->create();
        $plantInventoryLogFound = $this->service->show($plantInventoryLog->getKey());
        $plantInventoryLogFound->setAttribute('received_date', $plantInventoryLogFound->received_date->format('Y-m-d'));

        $this->assertNotNull($plantInventoryLogFound);
        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogFound);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogFound->getAttributes(), $plantInventoryLogFound->getFillable()));
        $this->assertTrue($plantInventoryLog->is($plantInventoryLogFound));
        $this->assertEquals($plantInventoryLog->getAttributes(), $plantInventoryLogFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->withDeleted()->create();
        $plantInventoryLogId = $plantInventoryLog->getKey();
        $plantInventoryLog->delete();
        $plantInventoryLogFoundWithTrash = $this->service->show($plantInventoryLogId, [], [], [], true);
        $plantInventoryLogFoundWithTrash->setAttribute('received_date', $plantInventoryLogFoundWithTrash->received_date->format('Y-m-d'));

        $this->assertNotNull($plantInventoryLogFoundWithTrash);
        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogFoundWithTrash);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogFoundWithTrash->getAttributes(), $plantInventoryLogFoundWithTrash->getFillable()));
        $this->assertTrue($plantInventoryLog->is($plantInventoryLogFoundWithTrash));
        $this->assertEquals($plantInventoryLog->getAttributes(), $plantInventoryLogFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($plantInventoryLog);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    private function setData($make = true, $hasDefectId = true)
    {
        $boxType = BoxType::factory()->create();
        $boxTypeAttributes = $boxType->getAttributes();
        $dataFactory = [
                'box_type_code' => $boxTypeAttributes['code'],
                'part_code' => $boxTypeAttributes['part_code'],
                'plant_code' => $boxTypeAttributes['plant_code'],
                'unit' => $boxTypeAttributes['unit'],
                'quantity' => $boxTypeAttributes['quantity'],
                'received_box_quantity' => 1
            ] + ($hasDefectId ? [] : ['defect_id' => null]);

        if (!$make) {
            $plantInventoryLog = PlantInventoryLog::factory()
                ->sequence(fn($sequence) => $dataFactory)->create();
        } else {
            $plantInventoryLog = PlantInventoryLog::factory()
                ->sequence(fn($sequence) => $dataFactory)->make();
        }

        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();

        $data = Arr::only($plantInventoryLogAttributes, [
            'part_code',
            'part_color_code',
            'box_type_code',
            'received_date',
            'warehouse_code',
            'defect_id',
            'plant_code'
        ]);

        return [$boxType, $boxTypeAttributes, $plantInventoryLog, $plantInventoryLogAttributes, $data];
    }

    public function test_store()
    {
        list($boxType, $boxTypeAttributes, $plantInventoryLog, $plantInventoryLogAttributes, $data) = $this->setData();

        $result = $this->service->store($data);
        $plantInventoryLogCreated = Arr::first($result);
        $plantInventoryLogCreated->setAttribute('received_date', $plantInventoryLogCreated->received_date->format('Y-m-d'));
        $plantInventoryLogCreatedAttributes = $plantInventoryLogCreated->getAttributes();
        $defectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => $plantInventoryLogAttributes['received_box_quantity'],
                'defect_id' => $plantInventoryLogAttributes['defect_id'],
                'part_defect_quantity' => null
            ])->forModel($plantInventoryLogCreated)->make();

        $this->validatePlantInventoryLog([$result, $plantInventoryLogCreated, $plantInventoryLogAttributes, $plantInventoryLog, $plantInventoryLogCreatedAttributes, $boxTypeAttributes, $boxType]);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));
    }

    public function test_store_with_remark()
    {
        list($boxType, $boxTypeAttributes, $plantInventoryLog, $plantInventoryLogAttributes, $data) = $this->setData();

        $upkwhInventoryLog = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $plantInventoryLogAttributes['part_code'],
                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                'box_type_code' => $plantInventoryLogAttributes['box_type_code'],
                'plant_code' => $plantInventoryLogAttributes['plant_code']
            ])->create();
        $remark = Remark::factory()->forModel($plantInventoryLog)->make();

        request()->merge(['remark' => $remark->content]);
        $result = $this->service->store($data);
        $plantInventoryLogCreated = Arr::first($result);
        $remarkCreated = $plantInventoryLogCreated->remarkable()->first();
        $plantInventoryLogCreated->setAttribute('received_date', $plantInventoryLogCreated->received_date->format('Y-m-d'));
        $plantInventoryLogCreatedAttributes = $plantInventoryLogCreated->getAttributes();
        $defectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => $plantInventoryLogAttributes['received_box_quantity'],
                'defect_id' => $plantInventoryLogAttributes['defect_id'],
                'part_defect_quantity' => null
            ])->forModel($plantInventoryLogCreated)->make();

        $this->validatePlantInventoryLog([$result, $plantInventoryLogCreated, $plantInventoryLogAttributes, $plantInventoryLog, $plantInventoryLogCreatedAttributes, $boxTypeAttributes, $boxType]);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLog->getAttributes(), $upkwhInventoryLog->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_store_case_update_data_already_exist()
    {
        list($boxType, $boxTypeAttributes, $plantInventoryLog, $plantInventoryLogAttributes, $data) = $this->setData(false);

        $result = $this->service->store($data);
        $plantInventoryLogCreated = Arr::first($result);
        $plantInventoryLogCreated->setAttribute('received_date', $plantInventoryLogCreated->received_date->format('Y-m-d'));
        $plantInventoryLogCreatedAttributes = $plantInventoryLogCreated->getAttributes();
        $plantInventoryLogAttributes['quantity'] += $boxTypeAttributes['quantity'];
        $plantInventoryLogAttributes['received_box_quantity'] += 1;
        $defectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => $plantInventoryLogAttributes['received_box_quantity'],
                'defect_id' => $plantInventoryLogAttributes['defect_id'],
                'part_defect_quantity' => null
            ])->forModel($plantInventoryLogCreated)->make();

        $this->validatePlantInventoryLog([$result, $plantInventoryLogCreated, $plantInventoryLogAttributes, $plantInventoryLog, $plantInventoryLogCreatedAttributes, $boxTypeAttributes, $boxType], false);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));
    }

    public function test_store_with_out_defect_id()
    {
        list($boxType, $boxTypeAttributes, $plantInventoryLog, $plantInventoryLogAttributes, $data) = $this->setData(true, false);

        $upkwhInventoryLog = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $plantInventoryLogAttributes['part_code'],
                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                'box_type_code' => $plantInventoryLogAttributes['box_type_code'],
                'plant_code' => $plantInventoryLogAttributes['plant_code']
            ])->create();
        $warehouseInventorySummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $plantInventoryLogAttributes['part_code'],
                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                'quantity' => $boxTypeAttributes['quantity'],
                'unit' => $plantInventoryLogAttributes['unit'],
                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH,
                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
                'plant_code' => $plantInventoryLogAttributes['plant_code']
            ])->make();


        $result = $this->service->store($data);
        $plantInventoryLogCreated = Arr::first($result);
        $plantInventoryLogCreated->setAttribute('received_date', $plantInventoryLogCreated->received_date->format('Y-m-d'));
        $plantInventoryLogCreatedAttributes = $plantInventoryLogCreated->getAttributes();

        $this->validatePlantInventoryLog([$result, $plantInventoryLogCreated, $plantInventoryLogAttributes, $plantInventoryLog, $plantInventoryLogCreatedAttributes, $boxTypeAttributes, $boxType], true, false);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLog->getAttributes(), $upkwhInventoryLog->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
    }

    private function validatePlantInventoryLog(array $validateData, $checkQuantity = true, $hasDefect = true)
    {
        list($result, $plantInventoryLogCreated, $plantInventoryLogAttributes, $plantInventoryLog, $plantInventoryLogCreatedAttributes, $boxTypeAttributes, $boxType) = $validateData;

        if ($hasDefect) {
            $plantInventoryLogAttributes['defect_id'] = 'W';
        }
        $this->assertIsArray($result);
        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogCreated);
        $this->assertArraySubset(Arr::only($plantInventoryLogAttributes, $plantInventoryLog->getFillable()), $plantInventoryLogCreatedAttributes);
        $this->assertDatabaseHas('box_types', Arr::only($boxTypeAttributes, $boxType->getFillable()));
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogCreatedAttributes, $plantInventoryLogCreated->getFillable()));
        if ($checkQuantity) {
            $this->assertTrue($plantInventoryLogCreated->getAttribute('quantity') == $boxTypeAttributes['quantity']);
            $this->assertTrue($plantInventoryLogCreated->getAttribute('received_box_quantity') == 1);
        }
    }

    public function test_update()
    {
        $plantInventoryLogOrigin = PlantInventoryLog::factory()->create();
        $plantInventoryLogOriginAttributes = $plantInventoryLogOrigin->getAttributes();
        //CHange only quantity
        $plantInventoryLogNew = PlantInventoryLog::factory()
            ->sequence(fn($sequence) => (
                Arr::only($plantInventoryLogOriginAttributes, [
                    'part_code',
                    'part_color_code',
                    'box_type_code',
                    'received_date',
                    'received_box_quantity',
                    'unit',
                    'warehouse_code',
                    'defect_id',
                    'plant_code'
                ])))->make();
        $plantInventoryLogNewAttributes = $plantInventoryLogNew->getAttributes();

        $data = Arr::only($plantInventoryLogNewAttributes, [
            'quantity'
        ]);

        $plantInventoryLogUpdated = $this->service->update($plantInventoryLogOrigin->getKey(), $data);
        $plantInventoryLogUpdated->setAttribute('received_date', $plantInventoryLogUpdated->received_date->format('Y-m-d'));
        $plantInventoryLogUpdatedAttributes = $plantInventoryLogUpdated->getAttributes();

        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogUpdated);
        $this->assertArraySubset(Arr::only($plantInventoryLogNewAttributes, $plantInventoryLogNew->getFillable()), $plantInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogUpdatedAttributes, $plantInventoryLogUpdated->getFillable()));
        if ($plantInventoryLogNewAttributes['quantity'] != $plantInventoryLogOrigin->getAttribute('quantity')) {
            $this->assertDatabaseMissing('plant_inventory_logs', Arr::only($plantInventoryLogOrigin->getAttributes(), $plantInventoryLogOrigin->getFillable()));
        }
    }

    public function test_update_with_remark()
    {
        $plantInventoryLogOrigin = PlantInventoryLog::factory()->create();
        $plantInventoryLogOriginAttributes = $plantInventoryLogOrigin->getAttributes();
        //CHange only quantity
        $plantInventoryLogNew = PlantInventoryLog::factory()
            ->sequence(fn($sequence) => (
            Arr::only($plantInventoryLogOriginAttributes, [
                'part_code',
                'part_color_code',
                'box_type_code',
                'received_date',
                'received_box_quantity',
                'unit',
                'warehouse_code',
                'defect_id',
                'plant_code'
            ])))->make();
        $plantInventoryLogNewAttributes = $plantInventoryLogNew->getAttributes();
        $remark = Remark::factory()->forModel($plantInventoryLogNew)->make();

        $data = Arr::only($plantInventoryLogNewAttributes, [
            'quantity'
        ]);

        request()->merge(['remark' => $remark->content]);
        $plantInventoryLogUpdated = $this->service->update($plantInventoryLogOrigin->getKey(), $data);
        $plantInventoryLogUpdated->setAttribute('received_date', $plantInventoryLogUpdated->received_date->format('Y-m-d'));
        $plantInventoryLogUpdatedAttributes = $plantInventoryLogUpdated->getAttributes();
        $remarkCreated = $plantInventoryLogUpdated->remarkable()->first();

        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($plantInventoryLogNewAttributes, $plantInventoryLogNew->getFillable()), $plantInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogUpdatedAttributes, $plantInventoryLogUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        if ($plantInventoryLogNewAttributes['quantity'] != $plantInventoryLogOrigin->getAttribute('quantity')) {
            $this->assertDatabaseMissing('plant_inventory_logs', Arr::only($plantInventoryLogOrigin->getAttributes(), $plantInventoryLogOrigin->getFillable()));
        }
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_soft()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();

        $result = $this->service->destroy($plantInventoryLog->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogAttributes, $plantInventoryLog->getFillable()));
        $this->assertDatabaseMissing('plant_inventory_logs', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($plantInventoryLog);
    }

    function test_destroy_soft_create_warehouse_adjustment_with_warehouse_summary_already_exist()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();

        $numberBoxDefect = mt_rand(1, 5);
        DefectInventory::factory()->forModel($plantInventoryLog)->count($numberBoxDefect)->create();

        $partQuantity = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
        $adjustmentQuantity =  round(-1 * ($plantInventoryLogAttributes['received_box_quantity'] - $numberBoxDefect) * $partQuantity);

        Warehouse::factory()->sequence(fn($sequence) => [
            'code' => $plantInventoryLogAttributes['warehouse_code'],
            'plant_code' => $plantInventoryLogAttributes['plant_code']
        ])->create();

        $warehouseInventorySummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $plantInventoryLogAttributes['part_code'],
                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
                'plant_code' => $plantInventoryLogAttributes['plant_code']
            ])->create();

        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();

        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()
            ->sequence(fn($sequence) => [
                'adjustment_quantity' => $adjustmentQuantity,
                'part_code' => $plantInventoryLogAttributes['part_code'],
                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
                'plant_code' => $plantInventoryLogAttributes['plant_code'],
                'old_quantity' => $warehouseInventorySummaryAttributes['quantity'],
                'new_quantity' => $warehouseInventorySummaryAttributes['quantity'] + intval($adjustmentQuantity)
            ])->make();

        $warehouseInventorySummaryAttributes['quantity'] += intval($adjustmentQuantity);

        request()->merge(['update_summary' => 1]);
        $result = $this->service->destroy($plantInventoryLog->getKey());

        $data = [
            'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
            'part_code' => $plantInventoryLogAttributes['part_code'],
            'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
            'plant_code' => $plantInventoryLogAttributes['plant_code']
        ];
        if ($adjustmentQuantity != 0) {
            $warehouseSummaryAdjustmentNew = WarehouseSummaryAdjustment::query()->where($data)->first();

            //autoCreateRemark
            $remark = Remark::factory()->forModel($warehouseSummaryAdjustmentNew)
                ->sequence(fn($sequence) => [
                    'content' => 'Automatically update quantity in Warehouse Summary'
                ])->make();
        }

        $this->assertTrue($result);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogAttributes, $plantInventoryLog->getFillable()));
        if ($adjustmentQuantity != 0) {
            $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustment->getAttributes(), $warehouseSummaryAdjustment->getFillable()));
            $this->assertDatabaseHas('remarks', Arr::only($remark->getAttributes(), $remark->getFillable()));
        }
        $this->assertDatabaseMissing('plant_inventory_logs', Arr::set($plantInventoryLogAttributes, 'deleted_at', null));
        $this->assertSoftDeleted($plantInventoryLog);
    }

    function test_destroy_soft_create_warehouse_adjustment_with_warehouse_summary_not_exist()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();

        $numberBoxDefect = mt_rand(3, 5);
        DefectInventory::factory()->forModel($plantInventoryLog)->count($numberBoxDefect)->create();

        $partQuantity = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
        $adjustmentQuantity =  round(-1 * ($plantInventoryLogAttributes['received_box_quantity'] - $numberBoxDefect) * $partQuantity);

        $warehouse = Warehouse::factory()->sequence(fn($sequence) => [
            'code' => $plantInventoryLogAttributes['warehouse_code'],
            'plant_code' => $plantInventoryLogAttributes['plant_code']
        ])->create();

        if ($adjustmentQuantity != 0) {
            $warehouseInventorySummary = WarehouseInventorySummary::factory()
                ->sequence(fn($sequence) => [
                    'part_code' => $plantInventoryLogAttributes['part_code'],
                    'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                    'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
                    'plant_code' => $plantInventoryLogAttributes['plant_code'],
                    'quantity' => $adjustmentQuantity,
                    'unit' => null,
                    'warehouse_type' => $warehouse->getAttribute('warehouse_type')
                ])->make();

            $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()
                ->sequence(fn($sequence) => [
                    'adjustment_quantity' => $adjustmentQuantity,
                    'part_code' => $plantInventoryLogAttributes['part_code'],
                    'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                    'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
                    'plant_code' => $plantInventoryLogAttributes['plant_code'],
                    'old_quantity' => 0,
                    'new_quantity' => $adjustmentQuantity
                ])->make();
        }


        request()->merge(['update_summary' => 1]);
        $result = $this->service->destroy($plantInventoryLog->getKey());

        if ($adjustmentQuantity != 0) {
            $data = [
                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
                'part_code' => $plantInventoryLogAttributes['part_code'],
                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
                'plant_code' => $plantInventoryLogAttributes['plant_code']
            ];

            $warehouseSummaryAdjustmentNew = WarehouseSummaryAdjustment::query()->where($data)->first();

            //autoCreateRemark
            $remark = Remark::factory()->forModel($warehouseSummaryAdjustmentNew)
                ->sequence(fn($sequence) => [
                    'content' => 'Automatically update quantity in Warehouse Summary'
                ])->make();
        }

        $this->assertTrue($result);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogAttributes, $plantInventoryLog->getFillable()));
        if ($adjustmentQuantity != 0) {
            $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
            $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustment->getAttributes(), $warehouseSummaryAdjustment->getFillable()));
            $this->assertDatabaseHas('remarks', Arr::only($remark->getAttributes(), $remark->getFillable()));
        }
        $this->assertDatabaseMissing('plant_inventory_logs', Arr::set($plantInventoryLogAttributes, 'deleted_at', null));
        $this->assertSoftDeleted($plantInventoryLog);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_columns()
    {
        $plantInventoryLogs = PlantInventoryLog::factory()->count(20)->create();
        $plantInventoryFillable = $plantInventoryLogs->first()->getFillable();
        $column = Arr::random($plantInventoryFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $plantInventoryLogs->first()->getAttribute($column),
            'per_page' => 20,
        ]);
        $params = request()->toArray();

        $plantInventoryLogQuery = PlantInventoryLog::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($plantInventoryLogQuery, $result);
    }

    public function test_columns_incorrect_column()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->make();

        request()->merge([
            'column' => 'incorrect_column',
            'keyword' => Str::random(5),
            'per_page' => 20,
        ]);

        $result = $this->service->getColumnValue();

        $params = request()->toArray();

        $this->assertTrue(!in_array($params['column'], $plantInventoryLog->getFillable()));
        $this->assertIsArray($result);
        $this->assertTrue(empty($result));
    }

    public function test_defects_fail_validate_box_list_defect()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();
        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
        //make $box['part_defect_quantity'] > $partQuantityInBox
        $defectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'part_defect_quantity' => intval($partQuantityInBox) + 1
            ])->forModel($plantInventoryLog)->make();
        $defectInventoryAttributes = $defectInventory->getAttributes();

        $box = $defectInventoryAttributes;
        $box['id'] = $box['box_id'];
        $defectId = $defectInventory->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $boxErrors[] = [
            'id' => $box['id'],
            'message' => 'The part_defect_quantity must not be greater than ' . $partQuantityInBox
        ];

        $result = $this->service->defects($plantInventoryLog->getKey(), $data);

        $this->assertIsArray($result);
        $this->assertArraySubset($result, [false, 'Defect data submitted is incorrect', $boxErrors]);
    }

    public function test_defects_with_remark()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();
        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
        $defectInventoryBox = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'part_defect_quantity' => mt_rand(1, $partQuantityInBox)
            ])->forModel($plantInventoryLog)->make();
        $defectInventoryBoxAttributes = $defectInventoryBox->getAttributes();
        $defectInventory = DefectInventory::factory()->make();

        $box = $defectInventoryBoxAttributes;
        $box['id'] = $box['box_id'];
        $defectId = $defectInventory->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $remark = Remark::factory()->forModel($plantInventoryLog)->make();
        request()->merge(['remark' => $remark->content]);

        $result = $this->service->defects($plantInventoryLog->getKey(), $data);
        $plantInventoryLogSave = Arr::first($result);
        $remarkCreated = $plantInventoryLogSave->remarkable()->first();

        $this->assertIsArray($result);
        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogSave);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogSave->getAttributes(), $plantInventoryLogSave->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_defects_update_warehouse_summary_by_defect_status_create_defect()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();

        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
        //make $box['id'] <= $plantLog->received_box_quantity
        $defectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'part_defect_quantity' => mt_rand(1, intval($partQuantityInBox)),
                'box_id' => mt_rand(1, intval($plantInventoryLogAttributes['received_box_quantity']))
            ])->forModel($plantInventoryLog)->make();
        $defectInventoryAttributes = $defectInventory->getAttributes();

        $box = $defectInventoryAttributes;
        $faker = Factory::create();
        $box['id'] = $box['box_id'];
        $box['remark'] = $faker->text;
        $defectId = $defectInventory->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $result = $this->service->defects($plantInventoryLog->getKey(), $data);
        $plantInventoryLogSave = Arr::first($result);
        $defectNew = DefectInventory::query()->where($defectInventoryAttributes)->first();
        $defectRemark = Remark::factory()
            ->sequence(fn($sequence) => [
                'content' => $box['remark']
            ])->forModel($defectNew)->make();

        $this->assertIsArray($result);
        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogSave);
        $this->assertDatabaseHas('plant_inventory_logs', Arr::only($plantInventoryLogSave->getAttributes(), $plantInventoryLogSave->getFillable()));
        $this->assertTrue($plantInventoryLogSave->getAttribute('defect_id') == 'W');
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryAttributes, $defectInventory->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($defectRemark->getAttributes(), $defectRemark->getFillable()));
    }

    public function test_defects_update_warehouse_summary_by_defect_status_update_defects() {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();

        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];

        //make $box['id'] <= $plantLog->received_box_quantity
        $defectInventoryUpdate = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'part_defect_quantity' => mt_rand(1, intval($partQuantityInBox)),
                'box_id' => mt_rand(1, intval($plantInventoryLogAttributes['received_box_quantity']))
            ])->forModel($plantInventoryLog)->make();
        $defectInventoryUpdateAttributes = $defectInventoryUpdate->getAttributes();

        $oldDefectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => $defectInventoryUpdateAttributes['box_id']
            ])->forModel($plantInventoryLog)->create();

        $box = $defectInventoryUpdateAttributes;
        $faker = Factory::create();
        $box['id'] = $box['box_id'];
        $box['remark'] = $faker->text;
        $defectId = $defectInventoryUpdate->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $result = $this->service->defects($plantInventoryLog->getKey(), $data);
        $plantInventoryLogSave = Arr::first($result);
        $defectRemark = Remark::factory()
            ->sequence(fn($sequence) => [
                'content' => $box['remark']
            ])->forModel($oldDefectInventory)->make();

        $this->assertIsArray($result);
        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogSave);
        $this->assertDatabaseHas('plant_inventory_logs', $plantInventoryLogSave->getAttributes());
        $this->assertTrue($plantInventoryLogSave->getAttribute('defect_id') == 'W');
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryUpdateAttributes, $defectInventoryUpdate->getFillable()));
        $this->assertDatabaseHas('remarks', $defectRemark->getAttributes());
    }

//    comment code, ko update summary theo task THAC-1154
//    public function test_defects_update_warehouse_summary_by_defect_status_new_defects()
//    {
//        $plantInventoryLog = PlantInventoryLog::factory()->create();
//        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();
//
//        $warehouseInventorySummary = WarehouseInventorySummary::factory()
//            ->sequence(fn($sequence) => [
//                'part_code' => $plantInventoryLogAttributes['part_code'],
//                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
//                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
//                'plant_code' => $plantInventoryLogAttributes['plant_code'],
//                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH,
//                'quantity' => $plantInventoryLogAttributes['quantity']
//            ])->create();
//        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();
//
//        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
//        //make $box['id'] <= $plantLog->received_box_quantity
//        $defectInventory = DefectInventory::factory()
//            ->sequence(fn($sequence) => [
//            'part_defect_quantity' => mt_rand(1, intval($partQuantityInBox)),
//            'box_id' => mt_rand(1, intval($plantInventoryLogAttributes['received_box_quantity']))
//        ])->forModel($plantInventoryLog)->make();
//        $defectInventoryAttributes = $defectInventory->getAttributes();
//
//        $box = $defectInventoryAttributes;
//        $faker = Factory::create();
//        $box['id'] = $box['box_id'];
//        $box['remark'] = $faker->text;
//        $defectId = $defectInventory->getAttribute('defect_id');
//        $data = [
//            'defect_id' => $defectId,
//            'box_list' => [$box]
//        ];
//
//        $result = $this->service->defects($plantInventoryLog->getKey(), $data);
//        $plantInventoryLogSave = Arr::first($result);
//        $defectNew = DefectInventory::query()->where($defectInventoryAttributes)->first();
//        $defectRemark = Remark::factory()
//            ->sequence(fn($sequence) => [
//                'content' => $box['remark']
//            ])->forModel($defectNew)->make();
//        $warehouseInventorySummaryAttributes['quantity'] -= $defectInventoryAttributes['part_defect_quantity'];
//
//        $this->assertIsArray($result);
//        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogSave);
//        $this->assertDatabaseHas('plant_inventory_logs', $plantInventoryLogSave->getAttributes());
//        $this->assertDatabaseHas('defect_inventories', $defectInventoryAttributes);
//        $this->assertDatabaseHas('warehouse_inventory_summaries', $warehouseInventorySummaryAttributes);
//        $this->assertDatabaseHas('remarks', $defectRemark->getAttributes());
//    }
//
//    public function test_defects_update_warehouse_summary_by_defect_status_old_defects()
//    {
//        $plantInventoryLog = PlantInventoryLog::factory()->create();
//        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();
//
//        $warehouseInventorySummary = WarehouseInventorySummary::factory()
//            ->sequence(fn($sequence) => [
//                'part_code' => $plantInventoryLogAttributes['part_code'],
//                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
//                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
//                'plant_code' => $plantInventoryLogAttributes['plant_code'],
//                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH,
//                'quantity' => $plantInventoryLogAttributes['quantity']
//            ])->create();
//        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();
//
//        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
//        //make $box['id'] <= $plantLog->received_box_quantity
//        $defectInventory = DefectInventory::factory()
//            ->sequence(fn($sequence) => [
//                'part_defect_quantity' => mt_rand(1, intval($partQuantityInBox)),
//                'box_id' => mt_rand(1, intval($plantInventoryLogAttributes['received_box_quantity']))
//            ])->forModel($plantInventoryLog)->make();
//        $defectInventoryAttributes = $defectInventory->getAttributes();
//
//        $defectInventoryOld = DefectInventory::factory()
//            ->sequence(fn($sequence) => [
//                'part_defect_quantity' => intval($defectInventoryAttributes['part_defect_quantity']) + mt_rand(1, 100),
//                'box_id' => $defectInventoryAttributes['box_id']
//            ])->forModel($plantInventoryLog)->create();
//        $defectInventoryOldAttributes = $defectInventoryOld->getAttributes();
//
//        $box = $defectInventoryAttributes;
//        $faker = Factory::create();
//        $box['id'] = $box['box_id'];
//        $box['remark'] = $faker->text;
//        $defectId = $defectInventory->getAttribute('defect_id');
//        $data = [
//            'defect_id' => $defectId,
//            'box_list' => [$box]
//        ];
//
//        $result = $this->service->defects($plantInventoryLog->getKey(), $data);
//        $plantInventoryLogSave = Arr::first($result);
//        $defectNew = DefectInventory::query()->where($defectInventoryAttributes)->first();
//        $defectRemark = Remark::factory()
//            ->sequence(fn($sequence) => [
//                'content' => $box['remark']
//            ])->forModel($defectNew)->make();
//        $decrementQuantity = ($defectInventoryAttributes['part_defect_quantity'] ?: 0) - ($defectInventoryOldAttributes['part_defect_quantity'] ?: 0);
//        $warehouseInventorySummaryAttributes['quantity'] -= $decrementQuantity;
//
//        $this->assertIsArray($result);
//        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogSave);
//        $this->assertDatabaseHas('plant_inventory_logs', $plantInventoryLogSave->getAttributes());
//        $this->assertDatabaseHas('defect_inventories', $defectInventoryAttributes);
//        $this->assertDatabaseHas('warehouse_inventory_summaries', $warehouseInventorySummaryAttributes);
//        $this->assertDatabaseHas('remarks', $defectRemark->getAttributes());
//    }
//
//    public function test_defects_update_warehouse_summary_by_defect_status_update_bulk()
//    {
//        $plantInventoryLog = PlantInventoryLog::factory()->create();
//        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();
//
//        $warehouseInventorySummary = WarehouseInventorySummary::factory()
//            ->sequence(fn($sequence) => [
//                'part_code' => $plantInventoryLogAttributes['part_code'],
//                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
//                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
//                'plant_code' => $plantInventoryLogAttributes['plant_code'],
//                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH,
//                'quantity' => $plantInventoryLogAttributes['quantity']
//            ])->create();
//        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();
//
//        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
//        //make $box['id'] <= $plantLog->received_box_quantity
//        $defectInventory = DefectInventory::factory()
//            ->sequence(fn($sequence) => [
//                'defect_id' => null,
//                'part_defect_quantity' => mt_rand(1, intval($partQuantityInBox)),
//                'box_id' => mt_rand(1, intval($plantInventoryLogAttributes['received_box_quantity']))
//            ])->forModel($plantInventoryLog)->make();
//        $defectInventoryAttributes = $defectInventory->getAttributes();
//
//        $defectInventoryOld = DefectInventory::factory()
//            ->sequence(fn($sequence) => [
//                'part_defect_quantity' => intval($defectInventoryAttributes['part_defect_quantity']) + mt_rand(1, 100),
//                'box_id' => $defectInventoryAttributes['box_id']
//            ])->forModel($plantInventoryLog)->create();
//        $defectInventoryOldAttributes = $defectInventoryOld->getAttributes();
//
//        $box = $defectInventoryAttributes;
//        $box['id'] = $box['box_id'];
//        $defectId = $defectInventory->getAttribute('defect_id');
//        $data = [
//            'defect_id' => $defectId,
//            'box_list' => [$box]
//        ];
//
//        $result = $this->service->defects($plantInventoryLog->getKey(), $data);
//        $plantInventoryLogSave = Arr::first($result);
//
//        $warehouseInventorySummaryUpdateAttributes = $warehouseInventorySummaryAttributes;
//        $warehouseInventorySummaryUpdateAttributes['quantity'] += $defectInventoryOldAttributes['part_defect_quantity'];
//
//        $this->assertIsArray($result);
//        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogSave);
//        $this->assertDatabaseHas('plant_inventory_logs', $plantInventoryLogSave->getAttributes());
//        $this->assertDatabaseHas('defect_inventories', $defectInventoryAttributes);
//        $this->assertDatabaseMissing('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryAttributes, $warehouseInventorySummary->getFillable()));
//        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryUpdateAttributes, $warehouseInventorySummary->getFillable()));
//    }
//
//    public function test_defects_update_warehouse_summary_by_defect_status_insert_bulk()
//    {
//        $plantInventoryLog = PlantInventoryLog::factory()->create();
//        $plantInventoryLogAttributes = $plantInventoryLog->getAttributes();
//
//        $warehouseInventorySummary = WarehouseInventorySummary::factory()
//            ->sequence(fn($sequence) => [
//                'part_code' => $plantInventoryLogAttributes['part_code'],
//                'part_color_code' => $plantInventoryLogAttributes['part_color_code'],
//                'warehouse_code' => $plantInventoryLogAttributes['warehouse_code'],
//                'plant_code' => $plantInventoryLogAttributes['plant_code'],
//                'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH,
//                'quantity' => $plantInventoryLogAttributes['quantity'],
//                'unit' => $plantInventoryLogAttributes['unit']
//            ])->make();
//        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();
//
//        $partQuantityInBox = $plantInventoryLogAttributes['quantity'] / $plantInventoryLogAttributes['received_box_quantity'];
//        //make $box['id'] <= $plantLog->received_box_quantity
//        $defectInventory = DefectInventory::factory()
//            ->sequence(fn($sequence) => [
//                'defect_id' => null,
//                'part_defect_quantity' => mt_rand(1, intval($partQuantityInBox)),
//                'box_id' => mt_rand(1, intval($plantInventoryLogAttributes['received_box_quantity']))
//            ])->forModel($plantInventoryLog)->make();
//        $defectInventoryAttributes = $defectInventory->getAttributes();
//
//        $defectInventoryOld = DefectInventory::factory()
//            ->sequence(fn($sequence) => [
//                'part_defect_quantity' => intval($defectInventoryAttributes['part_defect_quantity']) + mt_rand(1, 100),
//                'box_id' => $defectInventoryAttributes['box_id']
//            ])->forModel($plantInventoryLog)->create();
//        $defectInventoryOldAttributes = $defectInventoryOld->getAttributes();
//
//        $box = $defectInventoryAttributes;
//        $box['id'] = $box['box_id'];
//        $defectId = $defectInventory->getAttribute('defect_id');
//        $data = [
//            'defect_id' => $defectId,
//            'box_list' => [$box]
//        ];
//
//        $result = $this->service->defects($plantInventoryLog->getKey(), $data);
//        $plantInventoryLogSave = Arr::first($result);
//        $warehouseInventorySummaryAttributes['quantity'] = $defectInventoryOldAttributes['part_defect_quantity'];
//
//        $this->assertIsArray($result);
//        $this->assertInstanceOf(PlantInventoryLog::class, $plantInventoryLogSave);
//        $this->assertDatabaseHas('plant_inventory_logs', $plantInventoryLogSave->getAttributes());
//        $this->assertDatabaseHas('defect_inventories', $defectInventoryAttributes);
//        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryAttributes, $warehouseInventorySummary->getFillable()));
//    }

    public function test_defects_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->defects(1, []);
    }

    public function test_export()
    {
        PlantInventoryLog::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'plant-warehouse-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PlantExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        PlantInventoryLog::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'plant-warehouse-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PlantExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
