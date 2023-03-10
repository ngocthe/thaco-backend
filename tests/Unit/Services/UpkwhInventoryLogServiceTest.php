<?php

namespace Tests\Unit\Services;

use App\Exports\PlantExport;
use App\Models\Admin;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\DefectInventory;
use App\Models\upkwhInventoryLog;
use App\Models\Remark;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Models\WarehouseSummaryAdjustment;
use App\Services\UpkwhInventoryLogService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpkwhInventoryLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new UpkwhInventoryLogService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertTrue(\App\Models\UpkwhInventoryLog::class == $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        UpkwhInventoryLog::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $upkwhInventoryLogs = UpkwhInventoryLog::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $upkwhInventoryLogModel = new upkwhInventoryLog();
        $fillables = $upkwhInventoryLogModel->getFillable();

        $dataupkwhInventoryLog = $upkwhInventoryLogs->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataupkwhInventoryLog as $key => $upkwhInventoryLog) {
            $dataupkwhInventoryLog[$key] = Arr::only($upkwhInventoryLog, $fillables);
        }

        foreach ($dataResult as $key => $upkwhInventoryLog) {
            $dataResult[$key] = Arr::only($upkwhInventoryLog, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataupkwhInventoryLog, $dataResult);

    }

    public function test_paginate_has_search()
    {
        $upkwhInventoryLogs = UpkwhInventoryLog::factory()->count(20)->create();
        $upkwhInventoryLogAttributes = $upkwhInventoryLogs->first()->getAttributes();

        $params = [
            'contract_code' => $this->escapeLike($upkwhInventoryLogAttributes['contract_code']),
            'invoice_code' => $this->escapeLike($upkwhInventoryLogAttributes['invoice_code']),
            'bill_of_lading_code' => $this->escapeLike($upkwhInventoryLogAttributes['bill_of_lading_code']),
            'container_code' => $this->escapeLike($upkwhInventoryLogAttributes['container_code']),
            'case_code' => $this->escapeLike($upkwhInventoryLogAttributes['case_code']),
            'box_type_code' => $this->escapeLike($upkwhInventoryLogAttributes['box_type_code']),
            'supplier_code' => $this->escapeLike($upkwhInventoryLogAttributes['supplier_code']),
            'received_date' => $upkwhInventoryLogAttributes['received_date'],
            'warehouse_location_code' => $this->escapeLike($upkwhInventoryLogAttributes['warehouse_location_code']),
            'shipped_date' => $upkwhInventoryLogAttributes['shipped_date'],
            'part_code' => $this->escapeLike($upkwhInventoryLogAttributes['part_code']),
            'part_color_code' => $this->escapeLike($upkwhInventoryLogAttributes['part_color_code']),
            'plant_code' => $this->escapeLike($upkwhInventoryLogAttributes['plant_code']),
            'defect_id' => Arr::random([true, false]),
            'updated_at' => Carbon::parse($upkwhInventoryLogAttributes['updated_at'])->format('Y-m-d'),
            'page' => 1,
            'per_page' => 20
        ];

        $query = UpkwhInventoryLog::query()
            ->where('contract_code', 'LIKE', '%' . $params['contract_code'] . '%')
            ->where('invoice_code', 'LIKE', '%' . $params['invoice_code'] . '%')
            ->where('bill_of_lading_code', 'LIKE', '%' . $params['bill_of_lading_code'] . '%')
            ->where('container_code', 'LIKE', '%' . $params['container_code'] . '%')
            ->where('case_code', 'LIKE', '%' . $params['case_code'] . '%')
            ->where('box_type_code', 'LIKE', '%' . $params['box_type_code'] . '%')
            ->where('supplier_code', 'LIKE', '%' . $params['supplier_code'] . '%')
            ->where('received_date', $params['received_date'])
            ->where('warehouse_location_code', 'LIKE', '%' . $params['warehouse_location_code'] . '%')
            ->where('shipped_date', $params['shipped_date'])
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->whereDate('updated_at', '=', $params['updated_at']);

        if ($params['defect_id']) {
            $query->whereNotNull('defect_id');
        } else {
            $query->whereNull('defect_id');
        }

        $queryUpkwhInventoryLogs = $query->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $upkwhInventoryLogModel = new UpkwhInventoryLog();
        $fillables = $upkwhInventoryLogModel->getFillable();
        $fillables[] = 'updated_at';

        $dataUpkwhInventoryLog = $queryUpkwhInventoryLogs->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataUpkwhInventoryLog as $key => $upkwhInventoryLog) {
            $dataUpkwhInventoryLog[$key] = Arr::only($upkwhInventoryLog, $fillables);
        }

        foreach ($dataResult as $key => $upkwhInventoryLog) {
            $dataResult[$key] = Arr::only($upkwhInventoryLog, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataUpkwhInventoryLog, $dataResult);
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
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->withDeleted()->create();
        $upkwhInventoryLogFound = $this->service->show($upkwhInventoryLog->getKey());
        $upkwhInventoryLogFound->setAttribute('received_date', $upkwhInventoryLogFound->received_date->format('Y-m-d'));
        $upkwhInventoryLogFound->setAttribute('shipped_date', $upkwhInventoryLogFound->received_date->format('Y-m-d'));

        $this->assertNotNull($upkwhInventoryLogFound);
        $this->assertInstanceOf(UpkwhInventoryLog::class, $upkwhInventoryLogFound);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogFound->getAttributes(), $upkwhInventoryLogFound->getFillable()));
        $this->assertTrue($upkwhInventoryLog->is($upkwhInventoryLogFound));
        $this->assertEquals($upkwhInventoryLog->getAttributes(), $upkwhInventoryLogFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->withDeleted()->create();
        $upkwhInventoryLogId = $upkwhInventoryLog->getKey();
        $upkwhInventoryLog->delete();
        $upkwhInventoryLogFoundWithTrash = $this->service->show($upkwhInventoryLogId, [], [], [], true);
        $upkwhInventoryLogFoundWithTrash->setAttribute('received_date', $upkwhInventoryLogFoundWithTrash->received_date->format('Y-m-d'));
        $upkwhInventoryLogFoundWithTrash->setAttribute('shipped_date', $upkwhInventoryLogFoundWithTrash->received_date->format('Y-m-d'));

        $this->assertNotNull($upkwhInventoryLogFoundWithTrash);
        $this->assertInstanceOf(UpkwhInventoryLog::class, $upkwhInventoryLogFoundWithTrash);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogFoundWithTrash->getAttributes(), $upkwhInventoryLogFoundWithTrash->getFillable()));
        $this->assertTrue($upkwhInventoryLog->is($upkwhInventoryLogFoundWithTrash));
        $this->assertEquals($upkwhInventoryLog->getAttributes(), $upkwhInventoryLogFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($upkwhInventoryLog);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store_with_already_exist_bwh_order_request()
    {
        $bwhOrderRequest = BwhOrderRequest::factory()->create();
        $bwhOrderRequestAttributes = $bwhOrderRequest->getAttributes();

        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => [
            'contract_code' => $bwhOrderRequestAttributes['contract_code'],
            'invoice_code' => $bwhOrderRequestAttributes['invoice_code'],
            'bill_of_lading_code' => $bwhOrderRequestAttributes['bill_of_lading_code'],
            'container_code' => $bwhOrderRequestAttributes['container_code'],
            'case_code' => $bwhOrderRequestAttributes['case_code'],
            'part_code' => $bwhOrderRequestAttributes['part_code'],
            'part_color_code' => $bwhOrderRequestAttributes['part_color_code'],
            'box_type_code' => $bwhOrderRequestAttributes['box_type_code'],
            'plant_code' => $bwhOrderRequestAttributes['plant_code']
        ])->make();

        $result = $this->service->store($upkwhInventoryLog->getAttributes());

        $this->assertIsArray($result);
        $this->assertArraySubset($result, [false, 'There is already a bonded order request']);
    }

    public function test_store_with_bwh_inventory_log_not_exist()
    {
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->make();

        $result = $this->service->store($upkwhInventoryLog->getAttributes());

        $this->assertIsArray($result);
        $this->assertArraySubset($result, [false, 'There is no corresponding data in bonded inventory']);
    }

    public function test_store_with_remark()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'requested' => false,
            'defect_id' => null
        ])->create();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $uniqueData = $this->uniqueDataStore($bwhInventoryLogAttributes);

        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => $uniqueData)->make();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();
        $remark = Remark::factory()->forModel($upkwhInventoryLog)->make();
        request()->merge(['remark' => $remark->content]);

        $result = $this->service->store($upkwhInventoryLogAttributes);
        $upkwhInventoryLogCreated = Arr::first($result);
        $remarkCreated = $upkwhInventoryLogCreated->remarkable()->first();
        $bwhInventoryLog = BwhInventoryLog::query()->where($uniqueData)->first();
        $upkwhInventoryLogAttributes['part_quantity'] = $bwhInventoryLogAttributes['part_quantity'];
        $upkwhInventoryLogAttributes['unit'] = $bwhInventoryLogAttributes['unit'];
        $upkwhInventoryLogAttributes['supplier_code'] = $bwhInventoryLogAttributes['supplier_code'];

        $this->validateUpkwhInventoryLog([$result, $bwhInventoryLog, $upkwhInventoryLogCreated, $upkwhInventoryLogAttributes, $upkwhInventoryLog]);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));

    }

    public function test_store_out_defect_id_update_warehouse_inventory_summary()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'requested' => false
        ])->create();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $uniqueData = $this->uniqueDataStore($bwhInventoryLogAttributes);

        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => $uniqueData + [
                'defect_id' => null,
                'part_quantity' => $bwhInventoryLogAttributes['part_quantity'],
                'unit' => $bwhInventoryLogAttributes['unit'],
                'supplier_code' => $bwhInventoryLogAttributes['supplier_code']
            ])->make();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        $warehouseInventorySummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $upkwhInventoryLogAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
                'warehouse_type' => WarehouseInventorySummary::TYPE_UPKWH,
                'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
                'plant_code' => $upkwhInventoryLogAttributes['plant_code']
            ])->create();
        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();

        $result = $this->service->store($upkwhInventoryLogAttributes);
        $upkwhInventoryLogCreated = Arr::first($result);
        $bwhInventoryLog = BwhInventoryLog::query()->where($uniqueData)->first();
        $upkwhInventoryLogAttributes['part_quantity'] = $bwhInventoryLogAttributes['part_quantity'];
        $upkwhInventoryLogAttributes['unit'] = $bwhInventoryLogAttributes['unit'];
        $upkwhInventoryLogAttributes['supplier_code'] = $bwhInventoryLogAttributes['supplier_code'];
        $warehouseInventorySummaryAttributes['quantity'] += $upkwhInventoryLogAttributes['box_quantity'] * $upkwhInventoryLogAttributes['part_quantity'];

        $this->validateUpkwhInventoryLog([$result, $bwhInventoryLog, $upkwhInventoryLogCreated, $upkwhInventoryLogAttributes, $upkwhInventoryLog]);
        $this->assertDatabaseMissing('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryAttributes, $warehouseInventorySummary->getFillable()));
    }

    public function test_store_out_defect_id_insert_warehouse_inventory_summary()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'requested' => false
        ])->create();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $uniqueData = $this->uniqueDataStore($bwhInventoryLogAttributes);

        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => $uniqueData + [
                'defect_id' => null,
                'part_quantity' => $bwhInventoryLogAttributes['part_quantity'],
                'unit' => $bwhInventoryLogAttributes['unit'],
                'supplier_code' => $bwhInventoryLogAttributes['supplier_code']
            ])->make();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        $warehouseInventorySummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $upkwhInventoryLogAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
                'quantity' => $upkwhInventoryLogAttributes['box_quantity'] * $upkwhInventoryLogAttributes['part_quantity'],
                'unit' => $upkwhInventoryLogAttributes['unit'],
                'warehouse_type' => WarehouseInventorySummary::TYPE_UPKWH,
                'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
                'plant_code' => $upkwhInventoryLogAttributes['plant_code']
            ])->make();
        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();

        $result = $this->service->store($upkwhInventoryLogAttributes);
        $upkwhInventoryLogCreated = Arr::first($result);
        $bwhInventoryLog = BwhInventoryLog::query()->where($uniqueData)->first();
        $upkwhInventoryLogAttributes['part_quantity'] = $bwhInventoryLogAttributes['part_quantity'];
        $upkwhInventoryLogAttributes['unit'] = $bwhInventoryLogAttributes['unit'];
        $upkwhInventoryLogAttributes['supplier_code'] = $bwhInventoryLogAttributes['supplier_code'];

        $this->validateUpkwhInventoryLog([$result, $bwhInventoryLog, $upkwhInventoryLogCreated, $upkwhInventoryLogAttributes, $upkwhInventoryLog]);
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryAttributes, $warehouseInventorySummary->getFillable()));
    }

    public function test_store_with_defect()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'requested' => false
        ])->create();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $uniqueData = $this->uniqueDataStore($bwhInventoryLogAttributes);

        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => $uniqueData + [
                'part_quantity' => $bwhInventoryLogAttributes['part_quantity'],
                'unit' => $bwhInventoryLogAttributes['unit'],
                'supplier_code' => $bwhInventoryLogAttributes['supplier_code']
            ])->make();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();
        $boxQuantity = $upkwhInventoryLogAttributes['box_quantity'];

        $result = $this->service->store($upkwhInventoryLogAttributes);
        $upkwhInventoryLogCreated = Arr::first($result);
        $bwhInventoryLog = BwhInventoryLog::query()->where($uniqueData)->first();

        $defectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => mt_rand(1, $boxQuantity),
                'defect_id' => $upkwhInventoryLogAttributes['defect_id'],
                'part_defect_quantity' => null
            ])->forModel($upkwhInventoryLogCreated)->make();

        $this->validateUpkwhInventoryLog([$result, $bwhInventoryLog, $upkwhInventoryLogCreated, $upkwhInventoryLogAttributes, $upkwhInventoryLog]);
        $this->assertDatabaseCount('defect_inventories', $boxQuantity);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));
    }

    public function test_update_with_box_quantity_less_than_shipped_box_quantity()
    {
        $boxQuantity = mt_rand(1, 50);
        $upkwhInventoryLogOrigin = UpkwhInventoryLog::factory()->sequence(fn($sequence) => [
            'box_quantity' => $boxQuantity,
            'shipped_box_quantity' => mt_rand($boxQuantity + 1, 100)
        ])->create();
        $upkwhInventoryLogOriginAttributes = $upkwhInventoryLogOrigin->getAttributes();
        $attributeUpdate = [
            'received_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date'
        ];

        $upkwhInventoryLogNew = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => (
            Arr::only($upkwhInventoryLogOriginAttributes, $attributeUpdate)))->make();
        $upkwhInventoryLogNewAttributes = $upkwhInventoryLogNew->getAttributes();

        $data = Arr::only($upkwhInventoryLogNewAttributes, $attributeUpdate);

        $result = $this->service->update($upkwhInventoryLogOrigin->getKey(), $data);

        $this->assertIsArray($result);
        $this->assertTrue($upkwhInventoryLogOriginAttributes['box_quantity'] < $upkwhInventoryLogOriginAttributes['shipped_box_quantity']);
        $this->assertArraySubset($result, [false, 'Data cannot be updated once all boxes have been shipped']);
        $this->assertDatabaseMissing('upkwh_inventory_logs', Arr::only($upkwhInventoryLogNewAttributes, $upkwhInventoryLogNew->getFillable()));
    }

    private function uniqueDataStore($attributes)
    {
        return [
            'contract_code' => $attributes['contract_code'],
            'invoice_code' => $attributes['invoice_code'],
            'bill_of_lading_code' => $attributes['bill_of_lading_code'],
            'container_code' => $attributes['container_code'],
            'case_code' => $attributes['case_code'],
            'part_code' => $attributes['part_code'],
            'part_color_code' => $attributes['part_color_code'],
            'box_type_code' => $attributes['box_type_code'],
            'plant_code' => $attributes['plant_code']
        ];
    }

    private function validateUpkwhInventoryLog(array $validateData)
    {
        list($result, $bwhInventoryLog, $upkwhInventoryLogCreated, $upkwhInventoryLogAttributes, $upkwhInventoryLog) = $validateData;

        $this->assertIsArray($result);
        $this->assertTrue($bwhInventoryLog->getAttribute('requested') == true);
        $this->assertInstanceOf(UpkwhInventoryLog::class, $upkwhInventoryLogCreated);
        $this->assertArraySubset(Arr::only($upkwhInventoryLogAttributes, $upkwhInventoryLog->getFillable()), $upkwhInventoryLogCreated->getAttributes());
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogAttributes, $upkwhInventoryLog->getFillable()));
    }

    public function test_update_with_origin_has_defect()
    {
        //Not $upkLog->box_quantity < $upkLog->shipped_box_quantity
        $boxQuantity = mt_rand(50, 100);
        $upkwhInventoryLogOrigin = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'box_quantity' => $boxQuantity,
                'shipped_box_quantity' => mt_rand(1, $boxQuantity)
            ])->create();
        $upkwhInventoryLogOriginAttributes = $upkwhInventoryLogOrigin->getAttributes();
        $attributeUpdate = $this->attributeUpdate();

        $upkwhInventoryLogNew = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => array_diff_key($upkwhInventoryLogOriginAttributes, $attributeUpdate))
            ->make();
        $upkwhInventoryLogNewAttributes = $upkwhInventoryLogNew->getAttributes();

        $data = Arr::only($upkwhInventoryLogNewAttributes, $attributeUpdate);

        $result = $this->service->update($upkwhInventoryLogOrigin->getKey(), $data);
        list($upkwhInventoryLogUpdated, $upkwhInventoryLogUpdatedAttributes) = $this->setDataUpdate($result);

        $this->validateUpkwhInventoryLogUpdate([$result, $upkwhInventoryLogUpdated, $upkwhInventoryLogNewAttributes, $upkwhInventoryLogNew, $upkwhInventoryLogUpdatedAttributes]);
    }

    public function test_update_update_warehouse_inventory_summary_with_old_warehouse_code_is_null()
    {
        //Not $upkLog->box_quantity < $upkLog->shipped_box_quantity
        $boxQuantity = mt_rand(50, 100);
        $upkwhInventoryLogOrigin = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'defect_id' => null,
                'warehouse_code' => null,
                'box_quantity' => $boxQuantity,
                'shipped_box_quantity' => mt_rand(1, $boxQuantity)
            ])->create();
        $upkwhInventoryLogOriginAttributes = $upkwhInventoryLogOrigin->getAttributes();
        $attributeUpdate = $this->attributeUpdate();
        $upkwhInventoryLogNew = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => array_diff_key($upkwhInventoryLogOriginAttributes, $attributeUpdate))
            ->make();
        $upkwhInventoryLogNewAttributes = $upkwhInventoryLogNew->getAttributes();

        $data = Arr::only($upkwhInventoryLogNewAttributes, $attributeUpdate);

        $warehouseInventorySummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $upkwhInventoryLogNewAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogNewAttributes['part_color_code'],
                'quantity' => 1 * $upkwhInventoryLogNewAttributes['box_quantity'] * $upkwhInventoryLogNewAttributes['part_quantity'],
                'unit' => $upkwhInventoryLogNewAttributes['unit'],
                'warehouse_type' => WarehouseInventorySummary::TYPE_UPKWH,
                'warehouse_code' => $upkwhInventoryLogNewAttributes['warehouse_code'],
                'plant_code' => $upkwhInventoryLogNewAttributes['plant_code']
            ])->make();
        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();

        $result = $this->service->update($upkwhInventoryLogOrigin->getKey(), $data);
        list($upkwhInventoryLogUpdated, $upkwhInventoryLogUpdatedAttributes) = $this->setDataUpdate($result);

        $this->validateUpkwhInventoryLogUpdate([$result, $upkwhInventoryLogUpdated, $upkwhInventoryLogNewAttributes, $upkwhInventoryLogNew, $upkwhInventoryLogUpdatedAttributes]);
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryAttributes, $warehouseInventorySummary->getFillable()));
    }

    public function test_update_update_warehouse_inventory_summary_with_has_old_warehouse_code()
    {
        //Not $upkLog->box_quantity < $upkLog->shipped_box_quantity
        $boxQuantity = mt_rand(50, 100);
        $upkwhInventoryLogOrigin = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'defect_id' => null,
                'box_quantity' => $boxQuantity,
                'shipped_box_quantity' => mt_rand(1, $boxQuantity)
            ])->create();
        $upkwhInventoryLogOriginAttributes = $upkwhInventoryLogOrigin->getAttributes();
        $attributeUpdate = $this->attributeUpdate();
        $upkwhInventoryLogNew = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => array_diff_key($upkwhInventoryLogOriginAttributes, $attributeUpdate))
            ->make();
        $upkwhInventoryLogNewAttributes = $upkwhInventoryLogNew->getAttributes();

        if ($upkwhInventoryLogNewAttributes['warehouse_code'] == $upkwhInventoryLogOriginAttributes['warehouse_code']) {
            $upkwhInventoryLogNewAttributes['warehouse_code'] = $this->makeNewCode($upkwhInventoryLogOriginAttributes['warehouse_code']);
        }

        $data = Arr::only($upkwhInventoryLogNewAttributes, $attributeUpdate);

        $oldWarehouseCode = $upkwhInventoryLogOriginAttributes['warehouse_code'];

        $warehouseInventorySummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $upkwhInventoryLogNewAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogNewAttributes['part_color_code'],
                'quantity' => 1 * $upkwhInventoryLogNewAttributes['box_quantity'] * $upkwhInventoryLogNewAttributes['part_quantity'],
                'unit' => $upkwhInventoryLogNewAttributes['unit'],
                'warehouse_type' => WarehouseInventorySummary::TYPE_UPKWH,
                'warehouse_code' => $upkwhInventoryLogNewAttributes['warehouse_code'],
                'plant_code' => $upkwhInventoryLogNewAttributes['plant_code']
            ])->make();
        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();

        $warehouseInventorySummaryOldCode = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $upkwhInventoryLogNewAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogNewAttributes['part_color_code'],
                'quantity' => -1 * $upkwhInventoryLogNewAttributes['box_quantity'] * $upkwhInventoryLogNewAttributes['part_quantity'],
                'unit' => $upkwhInventoryLogNewAttributes['unit'],
                'warehouse_type' => WarehouseInventorySummary::TYPE_UPKWH,
                'warehouse_code' => $oldWarehouseCode,
                'plant_code' => $upkwhInventoryLogNewAttributes['plant_code']
            ])->make();
        $warehouseInventorySummaryOldCodeAttributes = $warehouseInventorySummaryOldCode->getAttributes();

        $result = $this->service->update($upkwhInventoryLogOrigin->getKey(), $data);
        list($upkwhInventoryLogUpdated, $upkwhInventoryLogUpdatedAttributes) = $this->setDataUpdate($result);

        $this->validateUpkwhInventoryLogUpdate([$result, $upkwhInventoryLogUpdated, $upkwhInventoryLogNewAttributes, $upkwhInventoryLogNew, $upkwhInventoryLogUpdatedAttributes]);
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryAttributes, $warehouseInventorySummary->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryOldCodeAttributes, $warehouseInventorySummaryOldCode->getFillable()));
    }

    private function makeNewCode($originCode)
    {
        $newCode = Str::random(5);
        if ($newCode == $originCode) {
            return $this->makeNewCode($originCode);
        }
        return $newCode;
    }

    public function test_update_with_remark()
    {
        //Not $upkLog->box_quantity < $upkLog->shipped_box_quantity
        $boxQuantity = mt_rand(50, 100);
        $upkwhInventoryLogOrigin = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'box_quantity' => $boxQuantity,
                'shipped_box_quantity' => mt_rand(1, $boxQuantity)
            ])->create();
        $upkwhInventoryLogOriginAttributes = $upkwhInventoryLogOrigin->getAttributes();
        $attributeUpdate = $this->attributeUpdate();

        $upkwhInventoryLogNew = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => array_diff_key($upkwhInventoryLogOriginAttributes, $attributeUpdate))
            ->make();
        $upkwhInventoryLogNewAttributes = $upkwhInventoryLogNew->getAttributes();
        $remark = Remark::factory()->forModel($upkwhInventoryLogNew)->make();

        $data = Arr::only($upkwhInventoryLogNewAttributes, $attributeUpdate);

        request()->merge(['remark' => $remark->content]);
        $result = $this->service->update($upkwhInventoryLogOrigin->getKey(), $data);
        list($upkwhInventoryLogUpdated, $upkwhInventoryLogUpdatedAttributes) = $this->setDataUpdate($result);
        $remarkCreated = $upkwhInventoryLogUpdated->remarkable()->first();

        $this->validateUpkwhInventoryLogUpdate([$result, $upkwhInventoryLogUpdated, $upkwhInventoryLogNewAttributes, $upkwhInventoryLogNew, $upkwhInventoryLogUpdatedAttributes]);
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    private function attributeUpdate()
    {
        return [
            'received_date' => 'received_date',
            'warehouse_location_code' => 'warehouse_location_code',
            'warehouse_code' => 'warehouse_code',
            'shipped_date' => 'shipped_date'
        ];
    }

    private function setDataUpdate($result)
    {
        $upkwhInventoryLogUpdated = Arr::first($result);
        $upkwhInventoryLogUpdated->setAttribute('received_date', $upkwhInventoryLogUpdated->received_date->format('Y-m-d'));
        $upkwhInventoryLogUpdated->setAttribute('shipped_date', $upkwhInventoryLogUpdated->received_date->format('Y-m-d'));
        $upkwhInventoryLogUpdatedAttributes = $upkwhInventoryLogUpdated->getAttributes();

        return [$upkwhInventoryLogUpdated, $upkwhInventoryLogUpdatedAttributes];
    }

    private function validateUpkwhInventoryLogUpdate(array $validateData)
    {
        list($result, $upkwhInventoryLogUpdated, $upkwhInventoryLogNewAttributes, $upkwhInventoryLogNew, $upkwhInventoryLogUpdatedAttributes) = $validateData;

        $this->assertIsArray($result);
        $this->assertInstanceOf(UpkwhInventoryLog::class, $upkwhInventoryLogUpdated);
        $this->assertArraySubset(Arr::only($upkwhInventoryLogNewAttributes, $upkwhInventoryLogNew->getFillable()), $upkwhInventoryLogUpdatedAttributes);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogUpdatedAttributes, $upkwhInventoryLogUpdated->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_with_has_shipped_date()
    {
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => [
            'shipped_date' => now()->format('Y-m-d')
        ])->create();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        $result = $this->service->destroy($upkwhInventoryLog->getKey());

        $this->assertFalse($result);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::set($upkwhInventoryLogAttributes, 'deleted_at', null));
    }

    public function test_destroy_soft()
    {
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => [
            'shipped_date' => null
        ])->create();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        $result = $this->service->destroy($upkwhInventoryLog->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogAttributes, $upkwhInventoryLog->getFillable()));
        $this->assertDatabaseMissing('upkwh_inventory_logs', Arr::set($upkwhInventoryLogAttributes, 'deleted_at', null));
        $this->assertSoftDeleted($upkwhInventoryLog);
    }

    function test_destroy_soft_create_warehouse_adjustment_with_warehouse_summary_already_exist()
    {
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => [
            'shipped_date' => null
        ])->create();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        $numberBoxDefect = mt_rand(1, 5);
        DefectInventory::factory()->forModel($upkwhInventoryLog)->count($numberBoxDefect)->create();

        $adjustmentQuantity = round(-1 * ($upkwhInventoryLogAttributes['box_quantity'] - $numberBoxDefect) * $upkwhInventoryLogAttributes['part_quantity']);

        Warehouse::factory()->sequence(fn($sequence) => [
            'code' => $upkwhInventoryLogAttributes['warehouse_code'],
            'plant_code' => $upkwhInventoryLogAttributes['plant_code']
        ])->create();

        $warehouseInventorySummary = WarehouseInventorySummary::factory()
            ->sequence(fn($sequence) => [
                'part_code' => $upkwhInventoryLogAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
                'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
                'plant_code' => $upkwhInventoryLogAttributes['plant_code']
            ])->create();

        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();

        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()
            ->sequence(fn($sequence) => [
                'adjustment_quantity' => $adjustmentQuantity,
                'part_code' => $upkwhInventoryLogAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
                'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
                'plant_code' => $upkwhInventoryLogAttributes['plant_code'],
                'old_quantity' => $warehouseInventorySummaryAttributes['quantity'],
                'new_quantity' => $warehouseInventorySummaryAttributes['quantity'] + intval($adjustmentQuantity)
            ])->make();

        $warehouseInventorySummaryAttributes['quantity'] += intval($adjustmentQuantity);

        request()->merge(['update_summary' => 1]);
        $result = $this->service->destroy($upkwhInventoryLog->getKey());

        $data = [
            'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
            'part_code' => $upkwhInventoryLogAttributes['part_code'],
            'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
            'plant_code' => $upkwhInventoryLogAttributes['plant_code']
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
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogAttributes, $upkwhInventoryLog->getFillable()));
        if ($adjustmentQuantity != 0) {
            $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustment->getAttributes(), $warehouseSummaryAdjustment->getFillable()));
            $this->assertDatabaseHas('remarks', Arr::only($remark->getAttributes(), $remark->getFillable()));
        }
        $this->assertDatabaseMissing('upkwh_inventory_logs', Arr::set($upkwhInventoryLogAttributes, 'deleted_at', null));
        $this->assertSoftDeleted($upkwhInventoryLog);
    }

    function test_destroy_soft_create_warehouse_adjustment_with_warehouse_summary_not_exist()
    {
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->sequence(fn($sequence) => [
            'shipped_date' => null
        ])->create();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        $numberBoxDefect = mt_rand(3, 5);
        DefectInventory::factory()->forModel($upkwhInventoryLog)->count($numberBoxDefect)->create();

        $adjustmentQuantity = round(-1 * ($upkwhInventoryLogAttributes['box_quantity'] - $numberBoxDefect) * $upkwhInventoryLogAttributes['part_quantity']);

        $warehouse = Warehouse::factory()->sequence(fn($sequence) => [
            'code' => $upkwhInventoryLogAttributes['warehouse_code'],
            'plant_code' => $upkwhInventoryLogAttributes['plant_code']
        ])->create();

        if ($adjustmentQuantity != 0) {
            $warehouseInventorySummary = WarehouseInventorySummary::factory()
                ->sequence(fn($sequence) => [
                    'part_code' => $upkwhInventoryLogAttributes['part_code'],
                    'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
                    'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
                    'plant_code' => $upkwhInventoryLogAttributes['plant_code'],
                    'quantity' => $adjustmentQuantity,
                    'unit' => null,
                    'warehouse_type' => $warehouse->getAttribute('warehouse_type')
                ])->make();

            $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()
                ->sequence(fn($sequence) => [
                    'adjustment_quantity' => $adjustmentQuantity,
                    'part_code' => $upkwhInventoryLogAttributes['part_code'],
                    'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
                    'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
                    'plant_code' => $upkwhInventoryLogAttributes['plant_code'],
                    'old_quantity' => 0,
                    'new_quantity' => $adjustmentQuantity
                ])->make();
        }


        request()->merge(['update_summary' => 1]);
        $result = $this->service->destroy($upkwhInventoryLog->getKey());

        if ($adjustmentQuantity != 0) {
            $data = [
                'warehouse_code' => $upkwhInventoryLogAttributes['warehouse_code'],
                'part_code' => $upkwhInventoryLogAttributes['part_code'],
                'part_color_code' => $upkwhInventoryLogAttributes['part_color_code'],
                'plant_code' => $upkwhInventoryLogAttributes['plant_code']
            ];

            $warehouseSummaryAdjustmentNew = WarehouseSummaryAdjustment::query()->where($data)->first();

            //autoCreateRemark
            $remark = Remark::factory()->forModel($warehouseSummaryAdjustmentNew)
                ->sequence(fn($sequence) => [
                    'content' => 'Automatically update quantity in Warehouse Summary'
                ])->make();
        }

        $this->assertTrue($result);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogAttributes, $upkwhInventoryLog->getFillable()));
        if ($adjustmentQuantity != 0) {
            $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
            $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustment->getAttributes(), $warehouseSummaryAdjustment->getFillable()));
            $this->assertDatabaseHas('remarks', Arr::only($remark->getAttributes(), $remark->getFillable()));
        }
        $this->assertDatabaseMissing('upkwh_inventory_logs', Arr::set($upkwhInventoryLogAttributes, 'deleted_at', null));
        $this->assertSoftDeleted($upkwhInventoryLog);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_columns()
    {
        $upkwhInventoryLogs = UpkwhInventoryLog::factory()->count(20)->create();
        $upkwhInventoryFillable = $upkwhInventoryLogs->first()->getFillable();
        $column = Arr::random($upkwhInventoryFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $upkwhInventoryLogs->first()->getAttribute($column),
            'per_page' => 20,
        ]);
        $params = request()->toArray();

        $upkwhInventoryLogQuery = UpkwhInventoryLog::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($upkwhInventoryLogQuery, $result);
    }

    public function test_columns_incorrect_column()
    {
        $upkwhInventoryLog = UpkwhInventoryLog::factory()->make();

        request()->merge([
            'column' => 'incorrect_column',
            'keyword' => Str::random(5),
            'per_page' => 20,
        ]);

        $result = $this->service->getColumnValue();

        $params = request()->toArray();

        $this->assertTrue(!in_array($params['column'], $upkwhInventoryLog->getFillable()));
        $this->assertIsArray($result);
        $this->assertTrue(empty($result));
    }

    public function test_defects_fail_validate_box_list_defect()
    {
        //make $upkLog->shipped_box_quantity >= $upkLog->box_quantity
        $shippedBoxQuantity = mt_rand(50, 100);
        $upkwhInventoryLog = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'shipped_box_quantity' => $shippedBoxQuantity,
                'box_quantity' => mt_rand(1, $shippedBoxQuantity)
            ])->create();

        $defectInventory = DefectInventory::factory()->forModel($upkwhInventoryLog)->make();
        $defectInventoryAttributes = $defectInventory->getAttributes();

        $box = $defectInventoryAttributes;
        $box['id'] = $box['box_id'];
        $defectId = $defectInventory->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $result = $this->service->defects($upkwhInventoryLog->getKey(), $data);

        $this->assertIsArray($result);
        $this->assertArraySubset($result, [false, 'Data cannot be updated once all boxes have been shipped']);
    }

    public function test_defects_with_remark()
    {
        //make $upkLog->shipped_box_quantity < $upkLog->box_quantity
        $shippedBoxQuantity = mt_rand(1, 50);
        $upkwhInventoryLog = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'shipped_box_quantity' => $shippedBoxQuantity,
                'box_quantity' => mt_rand($shippedBoxQuantity + 1, 100)
            ])->create();

        $defectInventory = DefectInventory::factory()->forModel($upkwhInventoryLog)->make();
        $defectInventoryAttributes = $defectInventory->getAttributes();

        $box = $defectInventoryAttributes;
        $box['id'] = $box['box_id'];
        $defectId = $defectInventory->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $remark = Remark::factory()->forModel($upkwhInventoryLog)->make();
        request()->merge(['remark' => $remark->content]);

        $result = $this->service->defects($upkwhInventoryLog->getKey(), $data);
        $upkwhInventoryLogSave = Arr::first($result);
        $remarkCreated = $upkwhInventoryLogSave->remarkable()->first();

        $this->assertIsArray($result);
        $this->assertInstanceOf(UpkwhInventoryLog::class, $upkwhInventoryLogSave);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogSave->getAttributes(), $upkwhInventoryLog->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_defects_update_warehouse_summary_by_defect_status_create_defect()
    {
        //make $upkLog->shipped_box_quantity < $upkLog->box_quantity
        $shippedBoxQuantity = mt_rand(1, 50);
        $upkwhInventoryLog = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'shipped_box_quantity' => $shippedBoxQuantity,
                'box_quantity' => mt_rand($shippedBoxQuantity + 1, 100)
            ])->create();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        //make $box['id'] <= $upkLog->box_quantity
        $defectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => mt_rand(1, intval($upkwhInventoryLogAttributes['box_quantity'])),
                'part_defect_quantity' => null
            ])->forModel($upkwhInventoryLog)->make();
        $defectInventoryAttributes = $defectInventory->getAttributes();

        $box = $defectInventoryAttributes;
        $box['id'] = $box['box_id'];
        $defectId = $defectInventory->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $result = $this->service->defects($upkwhInventoryLog->getKey(), $data);
        $upkwhInventoryLogSave = Arr::first($result);

        $this->assertIsArray($result);
        $this->assertInstanceOf(UpkwhInventoryLog::class, $upkwhInventoryLogSave);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogSave->getAttributes(), $upkwhInventoryLogSave->getFillable()));
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryAttributes, $defectInventory->getFillable()));
    }

    public function test_defects_update_warehouse_summary_by_defect_status_update_defects()
    {
        //make $upkLog->shipped_box_quantity < $upkLog->box_quantity
        $shippedBoxQuantity = mt_rand(1, 50);
        $upkwhInventoryLog = UpkwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'shipped_box_quantity' => $shippedBoxQuantity,
                'box_quantity' => mt_rand($shippedBoxQuantity + 1, 100)
            ])->create();
        $upkwhInventoryLogAttributes = $upkwhInventoryLog->getAttributes();

        //make $box['id'] <= $upkLog->box_quantity
        $defectInventoryUpdate = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => mt_rand(1, intval($upkwhInventoryLogAttributes['box_quantity'])),
                'part_defect_quantity' => null
            ])->forModel($upkwhInventoryLog)->make();
        $defectInventoryUpdateAttributes = $defectInventoryUpdate->getAttributes();

        $oldDefectInventory = DefectInventory::factory()
            ->sequence(fn($sequence) => [
                'box_id' => $defectInventoryUpdateAttributes['box_id'],
                'part_defect_quantity' => null
            ])->forModel($upkwhInventoryLog)->create();

        $box = $defectInventoryUpdateAttributes;
        $box['id'] = $box['box_id'];
        $defectId = $defectInventoryUpdate->getAttribute('defect_id');
        $data = [
            'defect_id' => $defectId,
            'box_list' => [$box]
        ];

        $result = $this->service->defects($upkwhInventoryLog->getKey(), $data);
        $upkwhInventoryLogSave = Arr::first($result);

        $this->assertIsArray($result);
        $this->assertInstanceOf(UpkwhInventoryLog::class, $upkwhInventoryLogSave);
        $this->assertDatabaseHas('upkwh_inventory_logs', Arr::only($upkwhInventoryLogSave->getAttributes(), $upkwhInventoryLogSave->getFillable()));
        $this->assertTrue($upkwhInventoryLogSave->getAttribute('defect_id') == $defectInventoryUpdateAttributes['defect_id']);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryUpdateAttributes, $defectInventoryUpdate->getFillable()));
        if ($oldDefectInventory->getAttribute('defect_id') != $defectInventoryUpdateAttributes['defect_id']) {
            $this->assertDatabaseMissing('defect_inventories', $oldDefectInventory->getAttributes());
        }
    }

    public function test_defects_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->defects(1, []);
    }

    public function test_export()
    {
        UpkwhInventoryLog::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'upkwh-warehouse-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PlantExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        UpkwhInventoryLog::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'upkwh-warehouse-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PlantExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
