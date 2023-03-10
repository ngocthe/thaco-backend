<?php

namespace Tests\Unit\Services;

use App\Exports\WarehouseExport;
use App\Models\Admin;
use App\Models\Remark;
use App\Models\Warehouse;
use App\Models\WarehouseSummaryAdjustment;
use App\Services\WarehouseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class WarehouseServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new WarehouseService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(Warehouse::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        Warehouse::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $warehouses = Warehouse::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $warehouseModel = new Warehouse();
        $fillables = $warehouseModel->getFillable();

        $dataWarehouse = $warehouses->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataWarehouse as $key => $warehouse) {
            $dataWarehouse[$key] = Arr::only($warehouse, $fillables);
        }

        foreach ($dataResult as $key => $warehouse) {
            $dataResult[$key] = Arr::only($warehouse, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataWarehouse, $dataResult);
    }

    public function test_paginate_has_search()
    {
        $warehouses = Warehouse::factory()->count(20)->create();
        $warehouseAttributes = $warehouses->first()->getAttributes();

        $params = [
            'warehouse_type' => $warehouseAttributes['warehouse_type'],
            'plant_code' => $this->escapeLike($warehouseAttributes['plant_code']),
            'page' => 1,
            'per_page' => 20
        ];

        $query = Warehouse::query()
            ->where('warehouse_type', $params['warehouse_type'])
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%');

        $queryWarehouses = $query->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $warehouseModel = new Warehouse();
        $fillables = $warehouseModel->getFillable();

        $dataUpkwhInventoryLog = $queryWarehouses->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataUpkwhInventoryLog as $key => $warehouse) {
            $dataUpkwhInventoryLog[$key] = Arr::only($warehouse, $fillables);
        }

        foreach ($dataResult as $key => $warehouse) {
            $dataResult[$key] = Arr::only($warehouse, $fillables);
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

    public function test_search_codes()
    {
        Warehouse::factory()->count(20)->create();
        request()->merge(['per_page' => 20]);
        $params = request()->toArray();

        $warehouses = Warehouse::query()->select('code')
            ->distinct()
            ->orderBy('code')
            ->limit($params['per_page'])
            ->pluck('code')
            ->toArray();

        $result = $this->service->searchCode();

        $this->assertArraySubset($warehouses, $result);
    }

    public function test_search_codes_with_code()
    {
        Warehouse::factory()->count(20)->create();
        request()->merge(['per_page' => 20, 'code' => Str::random(2)]);
        $params = request()->toArray();
        $code = $this->service->escapeLike($params['code']);

        $warehouses = Warehouse::query()->select('code')
            ->where('code', 'LIKE', '%' . $code . '%')
            ->distinct()
            ->orderBy('code')
            ->limit($params['per_page'])
            ->pluck('code')
            ->toArray();

        $result = $this->service->searchCode();

        $this->assertArraySubset($warehouses, $result);
    }

    public function test_show()
    {
        $warehouse = Warehouse::factory()->withDeleted()->create();
        $warehouseFound = $this->service->show($warehouse->getKey());

        $this->assertNotNull($warehouseFound);
        $this->assertInstanceOf(Warehouse::class, $warehouseFound);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseFound->getAttributes(), $warehouseFound->getFillable()));
        $this->assertTrue($warehouse->is($warehouseFound));
        $this->assertEquals($warehouse->getAttributes(), $warehouseFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $warehouse = Warehouse::factory()->withDeleted()->create();
        $warehouseId = $warehouse->getKey();
        $warehouse->delete();
        $warehouseFoundWithTrash = $this->service->show($warehouseId, [], [], [], true);

        $this->assertNotNull($warehouseFoundWithTrash);
        $this->assertInstanceOf(Warehouse::class, $warehouseFoundWithTrash);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseFoundWithTrash->getAttributes(), $warehouseFoundWithTrash->getFillable()));
        $this->assertTrue($warehouse->is($warehouseFoundWithTrash));
        $this->assertEquals($warehouse->getAttributes(), $warehouseFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($warehouse);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $warehouse = Warehouse::factory()->make();

        $data = Arr::only($warehouse->getAttributes(), ['code', 'description', 'warehouse_type', 'plant_code']);

        $warehouseCreated = $this->service->store($data);

        $this->assertInstanceOf(Warehouse::class, $warehouseCreated);
        $this->assertArraySubset(Arr::only($warehouse->getAttributes(), $warehouse->getFillable()), $warehouseCreated->getAttributes());
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseCreated->getAttributes(), $warehouseCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $warehouse = Warehouse::factory()->make();
        $remark = Remark::factory()->forModel($warehouse)->make();

        $data = Arr::only($warehouse->getAttributes(), ['code', 'description', 'warehouse_type', 'plant_code']);
        request()->merge(['remark' => $remark->content]);
        $warehouseCreated = $this->service->store($data);
        $remarkCreated = $warehouseCreated->remarkable()->first();

        $this->assertInstanceOf(Warehouse::class, $warehouseCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($warehouse->getAttributes(), $warehouse->getFillable()), $warehouseCreated->getAttributes());
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseCreated->getAttributes(), $warehouseCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $warehouseOrigin = Warehouse::factory()->create();
        //Change only description
        $warehouseNew = Warehouse::factory()
            ->sequence(fn($sequence) => array_diff_key($warehouseOrigin->getAttributes(), ['description' => 'description']))
            ->make();
        $warehouseNewAttributes = $warehouseNew->getAttributes();

        $data = Arr::only($warehouseNewAttributes, ['description']);

        $warehouseUpdated = $this->service->update($warehouseOrigin->getKey(), $data);
        $warehouseUpdatedAttributes = $warehouseUpdated->getAttributes();

        $this->assertInstanceOf(Warehouse::class, $warehouseUpdated);
        $this->assertArraySubset(Arr::only($warehouseNewAttributes, $warehouseNew->getFillable()), $warehouseUpdatedAttributes);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseUpdatedAttributes, $warehouseUpdated->getFillable()));
        $this->assertDatabaseMissing('warehouses', Arr::only($warehouseOrigin->getAttributes(), $warehouseOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $warehouseOrigin = Warehouse::factory()->create();
        //Change only description
        $warehouseNew = Warehouse::factory()
            ->sequence(fn($sequence) => array_diff_key($warehouseOrigin->getAttributes(), ['description' => 'description']))
            ->make();
        $remark = Remark::factory()->forModel($warehouseNew)->make();
        $warehouseNewAttributes = $warehouseNew->getAttributes();

        $data = Arr::only($warehouseNewAttributes, ['description']);

        request()->merge(['remark' => $remark->content]);
        $warehouseUpdated = $this->service->update($warehouseOrigin->getKey(), $data);
        $warehouseUpdatedAttributes = $warehouseUpdated->getAttributes();
        $remarkCreated = $warehouseUpdated->remarkable()->first();

        $this->assertInstanceOf(Warehouse::class, $warehouseUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($warehouseNewAttributes, $warehouseNew->getFillable()), $warehouseUpdatedAttributes);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseUpdatedAttributes, $warehouseUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('warehouses', Arr::only($warehouseOrigin->getAttributes(), $warehouseOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_hard()
    {
        $warehouse = Warehouse::factory()->create();

        $result = $this->service->destroy($warehouse->getKey(), true);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('warehouses', $warehouse->getAttributes());
        $this->assertDeleted($warehouse);
    }

    public function test_destroy_soft()
    {
        $warehouse = Warehouse::factory()->create();
        $attributes = $warehouse->getAttributes();

        $result = $this->service->destroy($warehouse->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('warehouses', $attributes);
        $this->assertDatabaseMissing('warehouses', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($warehouse);
    }

    public function test_destroy_with_class_relation_delete_already_exist()
    {
        $warehouse = Warehouse::factory()->create();

        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()
            ->sequence(fn($sequence) =>[
                'warehouse_code' => $warehouse->getAttribute('code')
            ])->create();
        $result = $this->service->destroy($warehouse->getKey());

        $this->assertFalse($result);
        $this->assertDatabaseHas('warehouses', $warehouse->getAttributes());
        $this->assertDatabaseHas('warehouse_summary_adjustments', $warehouseSummaryAdjustment->getAttributes());
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_restore()
    {
        $warehouse = Warehouse::factory()->withDeleted()->create();
        $attributes = $warehouse->getAttributes();
        $warehouseId = $warehouse->getKey();
        $warehouse->delete();

        $result = $this->service->restore($warehouseId);

        $this->assertTrue($result);
        $this->assertDatabaseHas('warehouses', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_find_by()
    {
        $warehouse = Warehouse::factory()->withDeleted()->create();

        $warehouseFound = $this->service->findBy($warehouse->getAttributes());

        $this->assertNotNull($warehouseFound);
        $this->assertInstanceOf(Warehouse::class, $warehouseFound);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseFound->getAttributes(), $warehouseFound->getFillable()));
        $this->assertTrue($warehouse->is($warehouseFound));
        $this->assertEquals($warehouse->getAttributes(), $warehouseFound->getAttributes());
    }

    public function test_find_by_id()
    {
        $warehouse = Warehouse::factory()->withDeleted()->create();

        $warehouseFound = $this->service->findById($warehouse->getKey());

        $this->assertNotNull($warehouseFound);
        $this->assertInstanceOf(Warehouse::class, $warehouseFound);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouseFound->getAttributes(), $warehouseFound->getFillable()));
        $this->assertTrue($warehouse->is($warehouseFound));
        $this->assertEquals($warehouse->getAttributes(), $warehouseFound->getAttributes());
    }

    public function test_first_or_create()
    {
        $warehouseMake = Warehouse::factory()->make();
        $attributes = $warehouseMake->getAttributes();

        $warehouse = $this->service->firstOrCreate([], $attributes);

        $this->assertNotNull($warehouse);
        $this->assertInstanceOf(Warehouse::class, $warehouse);
        $this->assertArraySubset(Arr::only($warehouse->getAttributes(), $warehouse->getFillable()), $attributes);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouse->getAttributes(), $warehouse->getFillable()));

    }

    public function test_first_or_create_with_data_already_exist()
    {
        $warehouseNew = Warehouse::factory()->withDeleted()->create();
        $attributes = $warehouseNew->getAttributes();

        $warehouse = $this->service->firstOrCreate($attributes, $attributes);

        $this->assertNotNull($warehouse);
        $this->assertInstanceOf(Warehouse::class, $warehouse);
        $this->assertTrue($warehouseNew->is($warehouse));
        $this->assertEquals($warehouseNew->getAttributes(), $warehouse->getAttributes());
        $this->assertDatabaseHas('warehouses', Arr::only($warehouse->getAttributes(), $warehouse->getFillable()));
    }

    public function test_update_or_create()
    {
        $warehouseMake = Warehouse::factory()->make();
        $attributes = $warehouseMake->getAttributes();

        $warehouse = $this->service->updateOrCreate([], $attributes);

        $this->assertNotNull($warehouse);
        $this->assertInstanceOf(Warehouse::class, $warehouse);
        $this->assertArraySubset(Arr::only($warehouse->getAttributes(), $warehouse->getFillable()), $attributes);
        $this->assertDatabaseHas('warehouses', Arr::only($warehouse->getAttributes(), $warehouse->getFillable()));

    }

    public function test_update_or_create_with_data_already_exist()
    {
        $warehouseNew = Warehouse::factory()->withDeleted()->create();
        $attributes = $warehouseNew->getAttributes();

        $warehouse = $this->service->updateOrCreate($attributes, $attributes);

        $this->assertNotNull($warehouse);
        $this->assertInstanceOf(Warehouse::class, $warehouse);
        $this->assertTrue($warehouseNew->is($warehouse));
        $this->assertEquals($warehouseNew->getAttributes(), $warehouse->getAttributes());
        $this->assertDatabaseHas('warehouses', Arr::only($warehouse->getAttributes(), $warehouse->getFillable()));
    }

    public function test_export()
    {
        Warehouse::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'warehouse-master';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), warehouseExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        Warehouse::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'warehouse-master';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), warehouseExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
