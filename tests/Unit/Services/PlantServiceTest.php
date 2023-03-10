<?php

namespace Tests\Unit\Services;

use App\Exports\PlantExport;
use App\Models\Admin;
use App\Models\Msc;
use App\Models\Plant;
use App\Models\Remark;
use App\Services\PlantService;
use Faker\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlantServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new PlantService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(Plant::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        Plant::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $plants = Plant::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $plantModel = new Plant();
        $fillables = $plantModel->getFillable();

        $dataPlant = $plants->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataPlant as $key => $plant) {
            $dataPlant[$key] = Arr::only($plant, $fillables);
        }

        foreach ($dataResult as $key => $plant) {
            $dataResult[$key] = Arr::only($plant, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataPlant, $dataResult);

    }

    public function test_search_codes()
    {
        Plant::factory()->count(20)->create();
        request()->merge(['per_page' => 20]);
        $params = request()->toArray();

        $plants = Plant::query()->select('code')
            ->distinct()
            ->orderBy('code')
            ->limit($params['per_page'])
            ->pluck('code')
            ->toArray();

        $result = $this->service->searchCode();

        $this->assertArraySubset($plants, $result);
    }

    public function test_search_codes_with_code()
    {
        Plant::factory()->count(20)->create();
        request()->merge(['per_page' => 20, 'code' => Str::random(2)]);
        $params = request()->toArray();
        $code = $this->service->escapeLike($params['code']);

        $plants = Plant::query()->select('code')
            ->where('code', 'LIKE', '%' . $code . '%')
            ->distinct()
            ->orderBy('code')
            ->limit($params['per_page'])
            ->pluck('code')
            ->toArray();

        $result = $this->service->searchCode();

        $this->assertArraySubset($plants, $result);
    }

    public function test_show()
    {
        $plant = Plant::factory()->withDeleted()->create();
        $plantFound = $this->service->show($plant->getKey());

        $this->assertNotNull($plantFound);
        $this->assertInstanceOf(Plant::class, $plantFound);
        $this->assertDatabaseHas('plants', Arr::only($plantFound->getAttributes(), $plantFound->getFillable()));
        $this->assertTrue($plant->is($plantFound));
        $this->assertEquals($plant->getAttributes(), $plantFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $plant = Plant::factory()->withDeleted()->create();
        $plantId = $plant->getKey();
        $plant->delete();
        $plantFoundWithTrash = $this->service->show($plantId, [], [], [], true);

        $this->assertNotNull($plantFoundWithTrash);
        $this->assertInstanceOf(Plant::class, $plantFoundWithTrash);
        $this->assertDatabaseHas('plants', Arr::only($plantFoundWithTrash->getAttributes(), $plantFoundWithTrash->getFillable()));
        $this->assertTrue($plant->is($plantFoundWithTrash));
        $this->assertEquals($plant->getAttributes(), $plantFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($plant);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $plant = Plant::factory()->make();

        $data = Arr::only($plant->getAttributes(), ['code', 'description']);

        $plantCreated = $this->service->store($data);

        $this->assertInstanceOf(Plant::class, $plantCreated);
        $this->assertArraySubset(Arr::only($plant->getAttributes(), $plant->getFillable()), $plantCreated->getAttributes());
        $this->assertDatabaseHas('plants', Arr::only($plantCreated->getAttributes(), $plantCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $plant = Plant::factory()->make();
        $remark = Remark::factory()->forModel($plant)->make();

        $data = Arr::only($plant->getAttributes(), ['code', 'description']);
        request()->merge(['remark' => $remark->content]);
        $plantCreated = $this->service->store($data);
        $remarkCreated = $plantCreated->remarkable()->first();

        $this->assertInstanceOf(Plant::class, $plantCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($plant->getAttributes(), $plant->getFillable()), $plantCreated->getAttributes());
        $this->assertDatabaseHas('plants', Arr::only($plantCreated->getAttributes(), $plantCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $plantOrigin = Plant::factory()->create();
        $plantNew = Plant::factory()->make();
        $plantNewAttributes = $plantNew->getAttributes();

        $data = Arr::only($plantNewAttributes, ['code', 'description']);

        $plantUpdated = $this->service->update($plantOrigin->getKey(), $data);
        $plantUpdatedAttributes = $plantUpdated->getAttributes();

        $this->assertInstanceOf(Plant::class, $plantUpdated);
        $this->assertArraySubset(Arr::only($plantNewAttributes, $plantNew->getFillable()), $plantUpdatedAttributes);
        $this->assertDatabaseHas('plants', Arr::only($plantUpdatedAttributes, $plantUpdated->getFillable()));
        $this->assertDatabaseMissing('plants', Arr::only($plantOrigin->getAttributes(), $plantOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $plantOrigin = Plant::factory()->create();
        $plantNew = Plant::factory()->make();
        $remark = Remark::factory()->forModel($plantNew)->make();
        $plantNewAttributes = $plantNew->getAttributes();

        $data = Arr::only($plantNewAttributes, ['code', 'description']);

        request()->merge(['remark' => $remark->content]);
        $plantUpdated = $this->service->update($plantOrigin->getKey(), $data);
        $plantUpdatedAttributes = $plantUpdated->getAttributes();
        $remarkCreated = $plantUpdated->remarkable()->first();

        $this->assertInstanceOf(Plant::class, $plantUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($plantNewAttributes, $plantNew->getFillable()), $plantUpdatedAttributes);
        $this->assertDatabaseHas('plants', Arr::only($plantUpdatedAttributes, $plantUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('plants', Arr::only($plantOrigin->getAttributes(), $plantOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_hard()
    {
        $plant = Plant::factory()->create();

        $result = $this->service->destroy($plant->getKey(), true);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('plants', $plant->getAttributes());
        $this->assertDeleted($plant);
    }

    public function test_destroy_soft()
    {
        $plant = Plant::factory()->create();
        $attributes = $plant->getAttributes();

        $result = $this->service->destroy($plant->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('plants', $attributes);
        $this->assertDatabaseMissing('plants', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($plant);
    }

    public function test_destroy_with_class_relation_delete_already_exist()
    {
        $plant = Plant::factory()->create();

        $faker = Factory::create();
        $plantCode = $plant->code;

        $msc = Msc::query()->firstOrCreate([
            'plant_code' => $plantCode,
            'code'  => Str::random(5),
            'description'  => $faker->text,
            'interior_color'  => 'None',
            'car_line'  => $faker->text(8),
            'model_grade'  => $faker->text(8),
            'body'  => $faker->text(8),
            'engine'  => $faker->text(8),
            'transmission'  => $faker->text(8)
        ]);
        $result = $this->service->destroy($plant->getKey());

        $this->assertFalse($result);
        $this->assertDatabaseHas('plants', $plant->getAttributes());
        $this->assertDatabaseHas('mscs', $msc->getAttributes());
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_restore()
    {
        $plant = Plant::factory()->withDeleted()->create();
        $attributes = $plant->getAttributes();
        $plantId = $plant->getKey();
        $plant->delete();

        $result = $this->service->restore($plantId);

        $this->assertTrue($result);
        $this->assertDatabaseHas('plants', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_find_by()
    {
        $plant = Plant::factory()->withDeleted()->create();

        $plantFound = $this->service->findBy($plant->getAttributes());

        $this->assertNotNull($plantFound);
        $this->assertInstanceOf(Plant::class, $plantFound);
        $this->assertDatabaseHas('plants', Arr::only($plantFound->getAttributes(), $plantFound->getFillable()));
        $this->assertTrue($plant->is($plantFound));
        $this->assertEquals($plant->getAttributes(), $plantFound->getAttributes());
    }

    public function test_find_by_id()
    {
        $plant = Plant::factory()->withDeleted()->create();

        $plantFound = $this->service->findById($plant->getKey());

        $this->assertNotNull($plantFound);
        $this->assertInstanceOf(Plant::class, $plantFound);
        $this->assertDatabaseHas('plants', Arr::only($plantFound->getAttributes(), $plantFound->getFillable()));
        $this->assertTrue($plant->is($plantFound));
        $this->assertEquals($plant->getAttributes(), $plantFound->getAttributes());
    }

    public function test_first_or_create()
    {
        $plantMake = Plant::factory()->make();
        $attributes = $plantMake->getAttributes();

        $plant = $this->service->firstOrCreate([], $attributes);

        $this->assertNotNull($plant);
        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertArraySubset(Arr::only($plant->getAttributes(), $plant->getFillable()), $attributes);
        $this->assertDatabaseHas('plants', Arr::only($plant->getAttributes(), $plant->getFillable()));

    }

    public function test_first_or_create_with_data_already_exist()
    {
        $plantNew = Plant::factory()->withDeleted()->create();
        $attributes = $plantNew->getAttributes();

        $plant = $this->service->firstOrCreate($attributes, $attributes);

        $this->assertNotNull($plant);
        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertTrue($plantNew->is($plant));
        $this->assertEquals($plantNew->getAttributes(), $plant->getAttributes());
        $this->assertDatabaseHas('plants', Arr::only($plant->getAttributes(), $plant->getFillable()));
    }

    public function test_update_or_create()
    {
        $plantMake = Plant::factory()->make();
        $attributes = $plantMake->getAttributes();

        $plant = $this->service->updateOrCreate([], $attributes);

        $this->assertNotNull($plant);
        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertArraySubset(Arr::only($plant->getAttributes(), $plant->getFillable()), $attributes);
        $this->assertDatabaseHas('plants', Arr::only($plant->getAttributes(), $plant->getFillable()));

    }

    public function test_update_or_create_with_data_already_exist()
    {
        $plantNew = Plant::factory()->withDeleted()->create();
        $attributes = $plantNew->getAttributes();

        $plant = $this->service->updateOrCreate($attributes, $attributes);

        $this->assertNotNull($plant);
        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertTrue($plantNew->is($plant));
        $this->assertEquals($plantNew->getAttributes(), $plant->getAttributes());
        $this->assertDatabaseHas('plants', Arr::only($plant->getAttributes(), $plant->getFillable()));
    }

    public function test_export()
    {
        Plant::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'plant-master';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PlantExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        Plant::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'plant-master';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), PlantExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
