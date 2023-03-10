<?php

namespace Tests\Unit\Services;

use App\Models\Admin;
use App\Models\DefectInventory;
use App\Models\PlantInventoryLog;
use App\Models\Remark;
use App\Services\DefectInventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DefectInventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new DefectInventoryService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(DefectInventory::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        DefectInventory::factory()->forModel($plantInventoryLog)->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $defectInventories = DefectInventory::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $defectInventoryModel = new DefectInventory();
        $fillables = $defectInventoryModel->getFillable();

        $dataDefectInventory = $defectInventories->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataDefectInventory as $key => $defectInventory) {
            $dataDefectInventory[$key] = Arr::only($defectInventory, $fillables);
        }

        foreach ($dataResult as $key => $defectInventory) {
            $dataResult[$key] = Arr::only($defectInventory, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataDefectInventory, $dataResult);
    }

    public function test_show()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->withDeleted()->create();
        $defectInventoryFound = $this->service->show($defectInventory->getKey());

        $this->assertNotNull($defectInventoryFound);
        $this->assertInstanceOf(DefectInventory::class, $defectInventoryFound);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryFound->getAttributes(), $defectInventoryFound->getFillable()));
        $this->assertTrue($defectInventory->is($defectInventoryFound));
        $this->assertEquals($defectInventory->getAttributes(), $defectInventoryFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->withDeleted()->create();
        $defectInventoryId = $defectInventory->getKey();
        $defectInventory->delete();
        $defectInventoryFoundWithTrash = $this->service->show($defectInventoryId, [], [], [], true);

        $this->assertNotNull($defectInventoryFoundWithTrash);
        $this->assertInstanceOf(DefectInventory::class, $defectInventoryFoundWithTrash);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryFoundWithTrash->getAttributes(), $defectInventoryFoundWithTrash->getFillable()));
        $this->assertTrue($defectInventory->is($defectInventoryFoundWithTrash));
        $this->assertEquals($defectInventory->getAttributes(), $defectInventoryFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($defectInventory);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->make();

        $data = Arr::only($defectInventory->getAttributes(), [
            'modelable_type',
            'modelable_id',
            'box_id',
            'defect_id',
            'part_defect_quantity'
        ]);

        $defectInventoryCreated = $this->service->store($data);

        $this->assertInstanceOf(DefectInventory::class, $defectInventoryCreated);
        $this->assertArraySubset(Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()), $defectInventoryCreated->getAttributes());
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryCreated->getAttributes(), $defectInventoryCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->make();
        $remark = Remark::factory()->forModel($defectInventory)->make();

        $data = Arr::only($defectInventory->getAttributes(), [
            'modelable_type',
            'modelable_id',
            'box_id',
            'defect_id',
            'part_defect_quantity'
        ]);
        request()->merge(['remark' => $remark->content]);
        $defectInventoryCreated = $this->service->store($data);
        $remarkCreated = $defectInventoryCreated->remarkable()->first();

        $this->assertInstanceOf(DefectInventory::class, $defectInventoryCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()), $defectInventoryCreated->getAttributes());
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryCreated->getAttributes(), $defectInventoryCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventoryOrigin = DefectInventory::factory()->forModel($plantInventoryLog)->create();

        $defectInventoryNew = DefectInventory::factory()->forModel($plantInventoryLog)
            ->sequence(fn($sequence) => array_diff_key($defectInventoryOrigin->getAttributes(), [
                'modelable_type' => true,
                'modelable_id' => true,
                'box_id' => true,
                'defect_id' => true,
                'part_defect_quantity' => true
            ]))
            ->make();
        $defectInventoryNewAttributes = $defectInventoryNew->getAttributes();

        $data = Arr::only($defectInventoryNewAttributes, [
            'modelable_type',
            'modelable_id',
            'box_id',
            'defect_id',
            'part_defect_quantity'
        ]);

        $defectInventoryUpdated = $this->service->update($defectInventoryOrigin->getKey(), $data);
        $defectInventoryUpdatedAttributes = $defectInventoryUpdated->getAttributes();

        $this->assertInstanceOf(DefectInventory::class, $defectInventoryUpdated);
        $this->assertArraySubset(Arr::only($defectInventoryNewAttributes, $defectInventoryNew->getFillable()), $defectInventoryUpdatedAttributes);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryUpdatedAttributes, $defectInventoryUpdated->getFillable()));
        $this->assertDatabaseMissing('defect_inventories', Arr::only($defectInventoryOrigin->getAttributes(), $defectInventoryOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventoryOrigin = DefectInventory::factory()->forModel($plantInventoryLog)->create();

        $defectInventoryNew = DefectInventory::factory()->forModel($plantInventoryLog)
            ->sequence(fn($sequence) => array_diff_key($defectInventoryOrigin->getAttributes(), [
                'modelable_type' => true,
                'modelable_id' => true,
                'box_id' => true,
                'defect_id' => true,
                'part_defect_quantity' => true
            ]))
            ->make();
        $remark = Remark::factory()->forModel($defectInventoryNew)->make();
        $defectInventoryNewAttributes = $defectInventoryNew->getAttributes();

        $data = Arr::only($defectInventoryNewAttributes, [
            'modelable_type',
            'modelable_id',
            'box_id',
            'defect_id',
            'part_defect_quantity'
        ]);

        request()->merge(['remark' => $remark->content]);
        $defectInventoryUpdated = $this->service->update($defectInventoryOrigin->getKey(), $data);
        $defectInventoryUpdatedAttributes = $defectInventoryUpdated->getAttributes();
        $remarkCreated = $defectInventoryUpdated->remarkable()->first();

        $this->assertInstanceOf(DefectInventory::class, $defectInventoryUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($defectInventoryNewAttributes, $defectInventoryNew->getFillable()), $defectInventoryUpdatedAttributes);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryUpdatedAttributes, $defectInventoryUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('defect_inventories', Arr::only($defectInventoryOrigin->getAttributes(), $defectInventoryOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_hard()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->create();

        $result = $this->service->destroy($defectInventory->getKey(), true);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('defect_inventories', $defectInventory->getAttributes());
        $this->assertDeleted($defectInventory);
    }

    public function test_destroy_soft()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->create();
        $attributes = $defectInventory->getAttributes();

        $result = $this->service->destroy($defectInventory->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('defect_inventories', $attributes);
        $this->assertDatabaseMissing('defect_inventories', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($defectInventory);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_restore()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->withDeleted()->create();
        $attributes = $defectInventory->getAttributes();
        $defectInventoryId = $defectInventory->getKey();
        $defectInventory->delete();

        $result = $this->service->restore($defectInventoryId);

        $this->assertTrue($result);
        $this->assertDatabaseHas('defect_inventories', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_find_by()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->withDeleted()->create();

        $defectInventoryFound = $this->service->findBy($defectInventory->getAttributes());

        $this->assertNotNull($defectInventoryFound);
        $this->assertInstanceOf(DefectInventory::class, $defectInventoryFound);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryFound->getAttributes(), $defectInventoryFound->getFillable()));
        $this->assertTrue($defectInventory->is($defectInventoryFound));
        $this->assertEquals($defectInventory->getAttributes(), $defectInventoryFound->getAttributes());
    }

    public function test_find_by_id()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventory = DefectInventory::factory()->forModel($plantInventoryLog)->withDeleted()->create();

        $defectInventoryFound = $this->service->findById($defectInventory->getKey());

        $this->assertNotNull($defectInventoryFound);
        $this->assertInstanceOf(DefectInventory::class, $defectInventoryFound);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventoryFound->getAttributes(), $defectInventoryFound->getFillable()));
        $this->assertTrue($defectInventory->is($defectInventoryFound));
        $this->assertEquals($defectInventory->getAttributes(), $defectInventoryFound->getAttributes());
    }

    public function test_first_or_create()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventoryMake = DefectInventory::factory()->forModel($plantInventoryLog)->make();
        $attributes = $defectInventoryMake->getAttributes();

        $defectInventory = $this->service->firstOrCreate([], $attributes);

        $this->assertNotNull($defectInventory);
        $this->assertInstanceOf(DefectInventory::class, $defectInventory);
        $this->assertArraySubset(Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()), $attributes);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));

    }

    public function test_first_or_create_with_data_already_exist()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventoryNew = DefectInventory::factory()->forModel($plantInventoryLog)->withDeleted()->create();
        $attributes = $defectInventoryNew->getAttributes();

        $defectInventory = $this->service->firstOrCreate($attributes, $attributes);

        $this->assertNotNull($defectInventory);
        $this->assertInstanceOf(DefectInventory::class, $defectInventory);
        $this->assertTrue($defectInventoryNew->is($defectInventory));
        $this->assertEquals($defectInventoryNew->getAttributes(), $defectInventory->getAttributes());
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));
    }

    public function test_update_or_create()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventoryMake = DefectInventory::factory()->forModel($plantInventoryLog)->make();
        $attributes = $defectInventoryMake->getAttributes();

        $defectInventory = $this->service->updateOrCreate([], $attributes);

        $this->assertNotNull($defectInventory);
        $this->assertInstanceOf(DefectInventory::class, $defectInventory);
        $this->assertArraySubset(Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()), $attributes);
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));

    }

    public function test_update_or_create_with_data_already_exist()
    {
        $plantInventoryLog = PlantInventoryLog::factory()->create();
        $defectInventoryNew = DefectInventory::factory()->forModel($plantInventoryLog)->withDeleted()->create();
        $attributes = $defectInventoryNew->getAttributes();

        $defectInventory = $this->service->updateOrCreate($attributes, $attributes);

        $this->assertNotNull($defectInventory);
        $this->assertInstanceOf(DefectInventory::class, $defectInventory);
        $this->assertTrue($defectInventoryNew->is($defectInventory));
        $this->assertEquals($defectInventoryNew->getAttributes(), $defectInventory->getAttributes());
        $this->assertDatabaseHas('defect_inventories', Arr::only($defectInventory->getAttributes(), $defectInventory->getFillable()));
    }
}
