<?php

namespace Tests\Unit\Services;

use App\Exports\WarehouseExport;
use App\Models\Admin;
use App\Models\BwhInventoryLog;
use App\Models\Remark;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Services\WarehouseLocationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class WarehouseLocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new WarehouseLocationService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(WarehouseLocation::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        WarehouseLocation::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $warehouseLocations = WarehouseLocation::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $warehouseLocationModel = new WarehouseLocation();
        $fillables = $warehouseLocationModel->getFillable();

        $dataWarehouseLocation = $warehouseLocations->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataWarehouseLocation as $key => $warehouseLocation) {
            $dataWarehouseLocation[$key] = Arr::only($warehouseLocation, $fillables);
        }

        foreach ($dataResult as $key => $warehouseLocation) {
            $dataResult[$key] = Arr::only($warehouseLocation, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataWarehouseLocation, $dataResult);
    }

    public function test_paginate_has_search()
    {
        $warehouseLocations = WarehouseLocation::factory()->count(20)->create();
        $warehouseLocationAttributes = $warehouseLocations->first()->getAttributes();

        $params = [
            'warehouse_code' => $this->escapeLike($warehouseLocationAttributes['warehouse_code']),
            'plant_code' => $this->escapeLike($warehouseLocationAttributes['plant_code']),
            'page' => 1,
            'per_page' => 20
        ];

        $query = WarehouseLocation::query()
            ->where('warehouse_code', 'LIKE', '%' . $params['warehouse_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%');

        $queryWarehouseLocations = $query->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $warehouseLocationModel = new WarehouseLocation();
        $fillables = $warehouseLocationModel->getFillable();

        $dataUpkwhInventoryLog = $queryWarehouseLocations->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataUpkwhInventoryLog as $key => $warehouseLocation) {
            $dataUpkwhInventoryLog[$key] = Arr::only($warehouseLocation, $fillables);
        }

        foreach ($dataResult as $key => $warehouseLocation) {
            $dataResult[$key] = Arr::only($warehouseLocation, $fillables);
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
        WarehouseLocation::factory()->count(20)->create();
        request()->merge(['per_page' => 20]);
        $params = request()->toArray();

        $warehouseLocations = WarehouseLocation::query()->select('code')
            ->distinct()
            ->orderBy('code')
            ->limit($params['per_page'])
            ->pluck('code')
            ->toArray();

        $result = $this->service->searchCode();

        $this->assertArraySubset($warehouseLocations, $result);
    }

    public function test_search_codes_with_code()
    {
        WarehouseLocation::factory()->count(20)->create();
        request()->merge(['per_page' => 20, 'code' => Str::random(2)]);
        $params = request()->toArray();
        $code = $this->service->escapeLike($params['code']);

        $warehouseLocations = WarehouseLocation::query()->select('code')
            ->where('code', 'LIKE', '%' . $code . '%')
            ->distinct()
            ->orderBy('code')
            ->limit($params['per_page'])
            ->pluck('code')
            ->toArray();

        $result = $this->service->searchCode();

        $this->assertArraySubset($warehouseLocations, $result);
    }

    public function test_show()
    {
        $warehouseLocation = WarehouseLocation::factory()->withDeleted()->create();
        $warehouseLocationFound = $this->service->show($warehouseLocation->getKey());

        $this->assertNotNull($warehouseLocationFound);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationFound);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationFound->getAttributes(), $warehouseLocationFound->getFillable()));
        $this->assertTrue($warehouseLocation->is($warehouseLocationFound));
        $this->assertEquals($warehouseLocation->getAttributes(), $warehouseLocationFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $warehouseLocation = WarehouseLocation::factory()->withDeleted()->create();
        $warehouseLocationId = $warehouseLocation->getKey();
        $warehouseLocation->delete();
        $warehouseLocationFoundWithTrash = $this->service->show($warehouseLocationId, [], [], [], true);

        $this->assertNotNull($warehouseLocationFoundWithTrash);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationFoundWithTrash);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationFoundWithTrash->getAttributes(), $warehouseLocationFoundWithTrash->getFillable()));
        $this->assertTrue($warehouseLocation->is($warehouseLocationFoundWithTrash));
        $this->assertEquals($warehouseLocation->getAttributes(), $warehouseLocationFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($warehouseLocation);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $warehouseLocation = WarehouseLocation::factory()->make();

        $data = Arr::only($warehouseLocation->getAttributes(), ['code', 'warehouse_code', 'description', 'plant_code']);

        $warehouseLocationCreated = $this->service->store($data);

        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationCreated);
        $this->assertArraySubset(Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()), $warehouseLocationCreated->getAttributes());
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationCreated->getAttributes(), $warehouseLocationCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $warehouseLocation = WarehouseLocation::factory()->make();
        $remark = Remark::factory()->forModel($warehouseLocation)->make();

        $data = Arr::only($warehouseLocation->getAttributes(), ['code', 'warehouse_code', 'description', 'plant_code']);
        request()->merge(['remark' => $remark->content]);
        $warehouseLocationCreated = $this->service->store($data);
        $remarkCreated = $warehouseLocationCreated->remarkable()->first();

        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()), $warehouseLocationCreated->getAttributes());
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationCreated->getAttributes(), $warehouseLocationCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $warehouseLocationOrigin = WarehouseLocation::factory()->create();
        //Change only description
        $warehouseLocationNew = WarehouseLocation::factory()
            ->sequence(fn($sequence) => array_diff_key($warehouseLocationOrigin->getAttributes(), ['description' => 'description']))
            ->make();
        $warehouseLocationNewAttributes = $warehouseLocationNew->getAttributes();

        $data = Arr::only($warehouseLocationNewAttributes, ['description']);

        $warehouseLocationUpdated = $this->service->update($warehouseLocationOrigin->getKey(), $data);
        $warehouseLocationUpdatedAttributes = $warehouseLocationUpdated->getAttributes();

        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationUpdated);
        $this->assertArraySubset(Arr::only($warehouseLocationNewAttributes, $warehouseLocationNew->getFillable()), $warehouseLocationUpdatedAttributes);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationUpdatedAttributes, $warehouseLocationUpdated->getFillable()));
        $this->assertDatabaseMissing('warehouse_locations', Arr::only($warehouseLocationOrigin->getAttributes(), $warehouseLocationOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $warehouseLocationOrigin = WarehouseLocation::factory()->create();
        //Change only description
        $warehouseLocationNew = WarehouseLocation::factory()
            ->sequence(fn($sequence) => array_diff_key($warehouseLocationOrigin->getAttributes(), ['description' => 'description']))
            ->make();
        $remark = Remark::factory()->forModel($warehouseLocationNew)->make();
        $warehouseLocationNewAttributes = $warehouseLocationNew->getAttributes();

        $data = Arr::only($warehouseLocationNewAttributes, ['description']);

        request()->merge(['remark' => $remark->content]);
        $warehouseLocationUpdated = $this->service->update($warehouseLocationOrigin->getKey(), $data);
        $warehouseLocationUpdatedAttributes = $warehouseLocationUpdated->getAttributes();
        $remarkCreated = $warehouseLocationUpdated->remarkable()->first();

        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($warehouseLocationNewAttributes, $warehouseLocationNew->getFillable()), $warehouseLocationUpdatedAttributes);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationUpdatedAttributes, $warehouseLocationUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('warehouse_locations', Arr::only($warehouseLocationOrigin->getAttributes(), $warehouseLocationOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_hard()
    {
        $warehouseLocation = WarehouseLocation::factory()->create();

        $result = $this->service->destroy($warehouseLocation->getKey(), true);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('warehouse_locations', $warehouseLocation->getAttributes());
        $this->assertDeleted($warehouseLocation);
    }

    public function test_destroy_soft()
    {
        $warehouseLocation = WarehouseLocation::factory()->create();
        $attributes = $warehouseLocation->getAttributes();

        $result = $this->service->destroy($warehouseLocation->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('warehouse_locations', $attributes);
        $this->assertDatabaseMissing('warehouse_locations', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($warehouseLocation);
    }

    public function test_destroy_with_class_relation_delete_already_exist()
    {
        $warehouseLocation = WarehouseLocation::factory()->create();

        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence(fn($sequence) => [
                'warehouse_location_code' => $warehouseLocation->getAttribute('code')
            ])->create();
        $result = $this->service->destroy($warehouseLocation->getKey());

        $this->assertFalse($result);
        $this->assertDatabaseHas('warehouse_locations', $warehouseLocation->getAttributes());
        $this->assertDatabaseHas('bwh_inventory_logs', $bwhInventoryLog->getAttributes());
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_restore()
    {
        $warehouseLocation = WarehouseLocation::factory()->withDeleted()->create();
        $attributes = $warehouseLocation->getAttributes();
        $warehouseLocationId = $warehouseLocation->getKey();
        $warehouseLocation->delete();

        $result = $this->service->restore($warehouseLocationId);

        $this->assertTrue($result);
        $this->assertDatabaseHas('warehouse_locations', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_find_by()
    {
        $warehouseLocation = WarehouseLocation::factory()->withDeleted()->create();

        $warehouseLocationFound = $this->service->findBy($warehouseLocation->getAttributes());

        $this->assertNotNull($warehouseLocationFound);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationFound);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationFound->getAttributes(), $warehouseLocationFound->getFillable()));
        $this->assertTrue($warehouseLocation->is($warehouseLocationFound));
        $this->assertEquals($warehouseLocation->getAttributes(), $warehouseLocationFound->getAttributes());
    }

    public function test_find_by_id()
    {
        $warehouseLocation = WarehouseLocation::factory()->withDeleted()->create();

        $warehouseLocationFound = $this->service->findById($warehouseLocation->getKey());

        $this->assertNotNull($warehouseLocationFound);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocationFound);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocationFound->getAttributes(), $warehouseLocationFound->getFillable()));
        $this->assertTrue($warehouseLocation->is($warehouseLocationFound));
        $this->assertEquals($warehouseLocation->getAttributes(), $warehouseLocationFound->getAttributes());
    }

    public function test_first_or_create()
    {
        $warehouseLocationMake = WarehouseLocation::factory()->make();
        $attributes = $warehouseLocationMake->getAttributes();

        $warehouseLocation = $this->service->firstOrCreate([], $attributes);

        $this->assertNotNull($warehouseLocation);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocation);
        $this->assertArraySubset(Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()), $attributes);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()));

    }

    public function test_first_or_create_with_data_already_exist()
    {
        $warehouseLocationNew = WarehouseLocation::factory()->withDeleted()->create();
        $attributes = $warehouseLocationNew->getAttributes();

        $warehouseLocation = $this->service->firstOrCreate($attributes, $attributes);

        $this->assertNotNull($warehouseLocation);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocation);
        $this->assertTrue($warehouseLocationNew->is($warehouseLocation));
        $this->assertEquals($warehouseLocationNew->getAttributes(), $warehouseLocation->getAttributes());
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()));
    }

    public function test_update_or_create()
    {
        $warehouseLocationMake = WarehouseLocation::factory()->make();
        $attributes = $warehouseLocationMake->getAttributes();

        $warehouseLocation = $this->service->updateOrCreate([], $attributes);

        $this->assertNotNull($warehouseLocation);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocation);
        $this->assertArraySubset(Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()), $attributes);
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()));

    }

    public function test_update_or_create_with_data_already_exist()
    {
        $warehouseLocationNew = WarehouseLocation::factory()->withDeleted()->create();
        $attributes = $warehouseLocationNew->getAttributes();

        $warehouseLocation = $this->service->updateOrCreate($attributes, $attributes);

        $this->assertNotNull($warehouseLocation);
        $this->assertInstanceOf(WarehouseLocation::class, $warehouseLocation);
        $this->assertTrue($warehouseLocationNew->is($warehouseLocation));
        $this->assertEquals($warehouseLocationNew->getAttributes(), $warehouseLocation->getAttributes());
        $this->assertDatabaseHas('warehouse_locations', Arr::only($warehouseLocation->getAttributes(), $warehouseLocation->getFillable()));
    }

    public function test_export()
    {
        WarehouseLocation::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'warehouse-location-master';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), warehouseExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        WarehouseLocation::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'warehouse-location-master';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), warehouseExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
