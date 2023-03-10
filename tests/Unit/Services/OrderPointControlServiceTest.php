<?php

namespace Tests\Unit\Services;

use App\Exports\OrderPointControlExport;
use App\Models\Admin;
use App\Models\OrderPointControl;
use App\Models\Remark;
use App\Services\OrderPointControlService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderPointControlServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new OrderPointControlService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(OrderPointControl::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        OrderPointControl::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];

        $orderPointControls = OrderPointControl::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $orderPointControlModel = new OrderPointControl();
        $fillables = $orderPointControlModel->getFillable();

        $dataOrderPointControl = $orderPointControls->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataOrderPointControl as $key => $orderPointControl) {
            $dataOrderPointControl[$key] = Arr::only($orderPointControl, $fillables);
        }

        foreach ($dataResult as $key => $orderPointControl) {
            $dataResult[$key] = Arr::only($orderPointControl, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataOrderPointControl, $dataResult);
    }

    public function test_paginate_has_search()
    {
        $orderPointControls = OrderPointControl::factory()->count(20)->create();
        $orderPointControlAttributes = $orderPointControls->first()->getAttributes();

        $params = [
            'part_code' => $this->escapeLike($orderPointControlAttributes['part_code']),
            'part_color_code' => $this->escapeLike($orderPointControlAttributes['part_color_code']),
            'plant_code' => $this->escapeLike($orderPointControlAttributes['plant_code']),
            'page' => 1,
            'per_page' => 20
        ];

        $query = OrderPointControl::query()
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%');

        $queryOrderPointControls = $query->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $orderPointControlModel = new OrderPointControl();
        $fillables = $orderPointControlModel->getFillable();

        $dataUpkwhInventoryLog = $queryOrderPointControls->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataUpkwhInventoryLog as $key => $orderPointControl) {
            $dataUpkwhInventoryLog[$key] = Arr::only($orderPointControl, $fillables);
        }

        foreach ($dataResult as $key => $orderPointControl) {
            $dataResult[$key] = Arr::only($orderPointControl, $fillables);
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
        $orderPointControl = OrderPointControl::factory()->withDeleted()->create();
        $orderPointControlFound = $this->service->show($orderPointControl->getKey());

        $this->assertNotNull($orderPointControlFound);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlFound);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlFound->getAttributes(), $orderPointControlFound->getFillable()));
        $this->assertTrue($orderPointControl->is($orderPointControlFound));
        $this->assertEquals($orderPointControl->getAttributes(), $orderPointControlFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $orderPointControl = OrderPointControl::factory()->withDeleted()->create();
        $orderPointControlId = $orderPointControl->getKey();
        $orderPointControl->delete();
        $orderPointControlFoundWithTrash = $this->service->show($orderPointControlId, [], [], [], true);

        $this->assertNotNull($orderPointControlFoundWithTrash);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlFoundWithTrash);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlFoundWithTrash->getAttributes(), $orderPointControlFoundWithTrash->getFillable()));
        $this->assertTrue($orderPointControl->is($orderPointControlFoundWithTrash));
        $this->assertEquals($orderPointControl->getAttributes(), $orderPointControlFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($orderPointControl);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $orderPointControl = OrderPointControl::factory()->make();

        $data = Arr::only($orderPointControl->getAttributes(), [
            'part_code',
            'part_color_code',
            'box_type_code',
            'standard_stock',
            'ordering_lot',
            'plant_code'
        ]);

        $orderPointControlCreated = $this->service->store($data);

        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlCreated);
        $this->assertArraySubset(Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()), $orderPointControlCreated->getAttributes());
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlCreated->getAttributes(), $orderPointControlCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $remark = Remark::factory()->forModel($orderPointControl)->make();

        $data = Arr::only($orderPointControl->getAttributes(), [
            'part_code',
            'part_color_code',
            'box_type_code',
            'standard_stock',
            'ordering_lot',
            'plant_code'
        ]);
        request()->merge(['remark' => $remark->content]);
        $orderPointControlCreated = $this->service->store($data);
        $remarkCreated = $orderPointControlCreated->remarkable()->first();

        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()), $orderPointControlCreated->getAttributes());
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlCreated->getAttributes(), $orderPointControlCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $orderPointControlOrigin = OrderPointControl::factory()->create();
        //Change only standard_stock and ordering_lot
        $orderPointControlNew = OrderPointControl::factory()
            ->sequence(fn($sequence) => array_diff_key($orderPointControlOrigin->getAttributes(), ['standard_stock' => true, 'ordering_lot' => true]))
            ->make();
        $orderPointControlNewAttributes = $orderPointControlNew->getAttributes();

        $data = Arr::only($orderPointControlNewAttributes, ['standard_stock', 'ordering_lot']);

        $orderPointControlUpdated = $this->service->update($orderPointControlOrigin->getKey(), $data);
        $orderPointControlUpdatedAttributes = $orderPointControlUpdated->getAttributes();

        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlUpdated);
        $this->assertArraySubset(Arr::only($orderPointControlNewAttributes, $orderPointControlNew->getFillable()), $orderPointControlUpdatedAttributes);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlUpdatedAttributes, $orderPointControlUpdated->getFillable()));
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlOrigin->getAttributes(), $orderPointControlOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $orderPointControlOrigin = OrderPointControl::factory()->create();
        //Change only standard_stock and ordering_lot
        $orderPointControlNew = OrderPointControl::factory()
            ->sequence(fn($sequence) => array_diff_key($orderPointControlOrigin->getAttributes(), ['standard_stock' => true, 'ordering_lot' => true]))
            ->make();
        $remark = Remark::factory()->forModel($orderPointControlNew)->make();
        $orderPointControlNewAttributes = $orderPointControlNew->getAttributes();

        $data = Arr::only($orderPointControlNewAttributes, ['standard_stock', 'ordering_lot']);

        request()->merge(['remark' => $remark->content]);
        $orderPointControlUpdated = $this->service->update($orderPointControlOrigin->getKey(), $data);
        $orderPointControlUpdatedAttributes = $orderPointControlUpdated->getAttributes();
        $remarkCreated = $orderPointControlUpdated->remarkable()->first();

        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($orderPointControlNewAttributes, $orderPointControlNew->getFillable()), $orderPointControlUpdatedAttributes);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlUpdatedAttributes, $orderPointControlUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlOrigin->getAttributes(), $orderPointControlOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_hard()
    {
        $orderPointControl = OrderPointControl::factory()->create();

        $result = $this->service->destroy($orderPointControl->getKey(), true);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('order_point_controls', $orderPointControl->getAttributes());
        $this->assertDeleted($orderPointControl);
    }

    public function test_destroy_soft()
    {
        $orderPointControl = OrderPointControl::factory()->create();
        $attributes = $orderPointControl->getAttributes();

        $result = $this->service->destroy($orderPointControl->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('order_point_controls', $attributes);
        $this->assertDatabaseMissing('order_point_controls', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($orderPointControl);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_restore()
    {
        $orderPointControl = OrderPointControl::factory()->withDeleted()->create();
        $attributes = $orderPointControl->getAttributes();
        $orderPointControlId = $orderPointControl->getKey();
        $orderPointControl->delete();

        $result = $this->service->restore($orderPointControlId);

        $this->assertTrue($result);
        $this->assertDatabaseHas('order_point_controls', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_find_by()
    {
        $orderPointControl = OrderPointControl::factory()->withDeleted()->create();

        $orderPointControlFound = $this->service->findBy($orderPointControl->getAttributes());

        $this->assertNotNull($orderPointControlFound);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlFound);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlFound->getAttributes(), $orderPointControlFound->getFillable()));
        $this->assertTrue($orderPointControl->is($orderPointControlFound));
        $this->assertEquals($orderPointControl->getAttributes(), $orderPointControlFound->getAttributes());
    }

    public function test_find_by_id()
    {
        $orderPointControl = OrderPointControl::factory()->withDeleted()->create();

        $orderPointControlFound = $this->service->findById($orderPointControl->getKey());

        $this->assertNotNull($orderPointControlFound);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControlFound);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlFound->getAttributes(), $orderPointControlFound->getFillable()));
        $this->assertTrue($orderPointControl->is($orderPointControlFound));
        $this->assertEquals($orderPointControl->getAttributes(), $orderPointControlFound->getAttributes());
    }

    public function test_first_or_create()
    {
        $orderPointControlMake = OrderPointControl::factory()->make();
        $attributes = $orderPointControlMake->getAttributes();

        $orderPointControl = $this->service->firstOrCreate([], $attributes);

        $this->assertNotNull($orderPointControl);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControl);
        $this->assertArraySubset(Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()), $attributes);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()));

    }

    public function test_first_or_create_with_data_already_exist()
    {
        $orderPointControlNew = OrderPointControl::factory()->withDeleted()->create();
        $attributes = $orderPointControlNew->getAttributes();

        $orderPointControl = $this->service->firstOrCreate($attributes, $attributes);

        $this->assertNotNull($orderPointControl);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControl);
        $this->assertTrue($orderPointControlNew->is($orderPointControl));
        $this->assertEquals($orderPointControlNew->getAttributes(), $orderPointControl->getAttributes());
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()));
    }

    public function test_update_or_create()
    {
        $orderPointControlMake = OrderPointControl::factory()->make();
        $attributes = $orderPointControlMake->getAttributes();

        $orderPointControl = $this->service->updateOrCreate([], $attributes);

        $this->assertNotNull($orderPointControl);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControl);
        $this->assertArraySubset(Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()), $attributes);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()));

    }

    public function test_update_or_create_with_data_already_exist()
    {
        $orderPointControlNew = OrderPointControl::factory()->withDeleted()->create();
        $attributes = $orderPointControlNew->getAttributes();

        $orderPointControl = $this->service->updateOrCreate($attributes, $attributes);

        $this->assertNotNull($orderPointControl);
        $this->assertInstanceOf(OrderPointControl::class, $orderPointControl);
        $this->assertTrue($orderPointControlNew->is($orderPointControl));
        $this->assertEquals($orderPointControlNew->getAttributes(), $orderPointControl->getAttributes());
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControl->getAttributes(), $orderPointControl->getFillable()));
    }

    public function test_export()
    {
        OrderPointControl::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'unpack-warehouse-order-control';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), OrderPointControlExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        OrderPointControl::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'unpack-warehouse-order-control';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), OrderPointControlExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
