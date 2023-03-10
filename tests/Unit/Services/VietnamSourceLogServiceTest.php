<?php

namespace Tests\Unit\Services;

use App\Exports\VietnamSourceRequestExport;
use App\Models\Admin;
use App\Models\Remark;
use App\Models\VietnamSourceLog;
use App\Services\VietnamSourceLogService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class VietnamSourceLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new VietnamSourceLogService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(VietnamSourceLog::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        VietnamSourceLog::factory()->count(20)->create();
        $params = [
            'page' => 1,
            'per_page' => 20
        ];
        $vietnamSourceLogs = VietnamSourceLog::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $vietnamSourceLogModel = new vietnamSourceLog();
        $fillables = $vietnamSourceLogModel->getFillable();

        $dataVietnamSourceLog = $vietnamSourceLogs->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataVietnamSourceLog as $key => $vietnamSourceLog) {
            $dataVietnamSourceLog[$key] = Arr::only($vietnamSourceLog, $fillables);
        }

        foreach ($dataResult as $key => $vietnamSourceLog) {
            $dataResult[$key] = Arr::only($vietnamSourceLog, $fillables);
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($dataVietnamSourceLog, $dataResult);
    }

    public function test_paginate_has_search()
    {
        $vietnamSourceLogs = VietnamSourceLog::factory()->count(20)->create();
        $vietnamSourceLogAttributes = $vietnamSourceLogs->first()->getAttributes();

        $params = [
            'contract_code' => $this->escapeLike($vietnamSourceLogAttributes['contract_code']),
            'invoice_code' => $this->escapeLike($vietnamSourceLogAttributes['invoice_code']),
            'bill_of_lading_code' => $this->escapeLike($vietnamSourceLogAttributes['bill_of_lading_code']),
            'container_code' => $this->escapeLike($vietnamSourceLogAttributes['container_code']),
            'case_code' => $this->escapeLike($vietnamSourceLogAttributes['case_code']),
            'supplier_code' => $this->escapeLike($vietnamSourceLogAttributes['supplier_code']),
            'delivery_date' => Carbon::parse($vietnamSourceLogAttributes['delivery_date'])->format('Y-m-d'),
            'part_code' => $this->escapeLike($vietnamSourceLogAttributes['part_code']),
            'part_color_code' => $this->escapeLike($vietnamSourceLogAttributes['part_color_code']),
            'plant_code' => $this->escapeLike($vietnamSourceLogAttributes['plant_code']),
            'updated_at' => Carbon::parse($vietnamSourceLogAttributes['updated_at'])->format('Y-m-d'),
            'page' => 1,
            'per_page' => 20
        ];

        $query = VietnamSourceLog::query()
            ->where('contract_code', 'LIKE', '%' . $params['contract_code'] . '%')
            ->where('invoice_code', 'LIKE', '%' . $params['invoice_code'] . '%')
            ->where('bill_of_lading_code', 'LIKE', '%' . $params['bill_of_lading_code'] . '%')
            ->where('container_code', 'LIKE', '%' . $params['container_code'] . '%')
            ->where('case_code', 'LIKE', '%' . $params['case_code'] . '%')
            ->where('supplier_code', 'LIKE', '%' . $params['supplier_code'] . '%')
            ->whereDate('delivery_date', '=', $params['delivery_date'])
            ->where('part_code', 'LIKE', '%' . $params['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $params['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $params['plant_code'] . '%')
            ->whereDate('updated_at', '=', $params['updated_at']);

        $queryVietnamSourceLogs = $query->limit($params['per_page'])->offset(0)->latest('id')->get();
        $result = $this->service->paginate($params);

        $vietnamSourceLogModel = new vietnamSourceLog();
        $fillables = $vietnamSourceLogModel->getFillable();

        $dataUpkwhInventoryLog = $queryVietnamSourceLogs->toArray();
        $dataResult = $result->toArray()['data'];

        foreach ($dataUpkwhInventoryLog as $key => $vietnamSourceLog) {
            $dataUpkwhInventoryLog[$key] = Arr::only($vietnamSourceLog, $fillables);
        }

        foreach ($dataResult as $key => $vietnamSourceLog) {
            $dataResult[$key] = Arr::only($vietnamSourceLog, $fillables);
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
        $vietnamSourceLog = VietnamSourceLog::factory()->withDeleted()->create();
        $vietnamSourceLogFound = $this->service->show($vietnamSourceLog->getKey());

        $this->assertNotNull($vietnamSourceLogFound);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogFound);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogFound->getAttributes(), $vietnamSourceLogFound->getFillable()));
        $this->assertTrue($vietnamSourceLog->is($vietnamSourceLogFound));
        $this->assertEquals($vietnamSourceLog->getAttributes(), $vietnamSourceLogFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->withDeleted()->create();
        $vietnamSourceLogId = $vietnamSourceLog->getKey();
        $vietnamSourceLog->delete();
        $vietnamSourceLogFoundWithTrash = $this->service->show($vietnamSourceLogId, [], [], [], true);

        $this->assertNotNull($vietnamSourceLogFoundWithTrash);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogFoundWithTrash);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogFoundWithTrash->getAttributes(), $vietnamSourceLogFoundWithTrash->getFillable()));
        $this->assertTrue($vietnamSourceLog->is($vietnamSourceLogFoundWithTrash));
        $this->assertEquals($vietnamSourceLog->getAttributes(), $vietnamSourceLogFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($vietnamSourceLog);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->make();

        $data = Arr::only($vietnamSourceLog->getAttributes(), [
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
            'delivery_date',
            'plant_code'
        ]);

        $vietnamSourceLogCreated = $this->service->store($data);

        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogCreated);
        $this->assertArraySubset(Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()), $vietnamSourceLogCreated->getAttributes());
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogCreated->getAttributes(), $vietnamSourceLogCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->make();
        $remark = Remark::factory()->forModel($vietnamSourceLog)->make();

        $data = Arr::only($vietnamSourceLog->getAttributes(), [
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
            'delivery_date',
            'plant_code'
        ]);
        request()->merge(['remark' => $remark->content]);
        $vietnamSourceLogCreated = $this->service->store($data);
        $remarkCreated = $vietnamSourceLogCreated->remarkable()->first();

        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()), $vietnamSourceLogCreated->getAttributes());
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogCreated->getAttributes(), $vietnamSourceLogCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $vietnamSourceLogOrigin = VietnamSourceLog::factory()->create();
        //Change only description
        $vietnamSourceLogNew = VietnamSourceLog::factory()
            ->sequence(fn($sequence) => array_diff_key($vietnamSourceLogOrigin->getAttributes(), [
                'box_quantity' => true,
                'part_quantity' => true,
                'unit' => true,
                'supplier_code' => true,
                'delivery_date' => true
            ]))
            ->make();
        $vietnamSourceLogNewAttributes = $vietnamSourceLogNew->getAttributes();

        $data = Arr::only($vietnamSourceLogNewAttributes, [
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'delivery_date'
        ]);

        $vietnamSourceLogUpdated = $this->service->update($vietnamSourceLogOrigin->getKey(), $data);
        $vietnamSourceLogUpdatedAttributes = $vietnamSourceLogUpdated->getAttributes();

        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogUpdated);
        $this->assertArraySubset(Arr::only($vietnamSourceLogNewAttributes, $vietnamSourceLogNew->getFillable()), $vietnamSourceLogUpdatedAttributes);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogUpdatedAttributes, $vietnamSourceLogUpdated->getFillable()));
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourceLogOrigin->getAttributes(), $vietnamSourceLogOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $vietnamSourceLogOrigin = VietnamSourceLog::factory()->create();
        //Change only description
        $vietnamSourceLogNew = VietnamSourceLog::factory()
            ->sequence(fn($sequence) => array_diff_key($vietnamSourceLogOrigin->getAttributes(), [
                'box_quantity' => true,
                'part_quantity' => true,
                'unit' => true,
                'supplier_code' => true,
                'delivery_date' => true
            ]))
            ->make();
        $remark = Remark::factory()->forModel($vietnamSourceLogNew)->make();
        $vietnamSourceLogNewAttributes = $vietnamSourceLogNew->getAttributes();

        $data = Arr::only($vietnamSourceLogNewAttributes, [
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'delivery_date'
        ]);

        request()->merge(['remark' => $remark->content]);
        $vietnamSourceLogUpdated = $this->service->update($vietnamSourceLogOrigin->getKey(), $data);
        $vietnamSourceLogUpdatedAttributes = $vietnamSourceLogUpdated->getAttributes();
        $remarkCreated = $vietnamSourceLogUpdated->remarkable()->first();

        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($vietnamSourceLogNewAttributes, $vietnamSourceLogNew->getFillable()), $vietnamSourceLogUpdatedAttributes);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogUpdatedAttributes, $vietnamSourceLogUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourceLogOrigin->getAttributes(), $vietnamSourceLogOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_hard()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->create();

        $result = $this->service->destroy($vietnamSourceLog->getKey(), true);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('vietnam_source_logs', $vietnamSourceLog->getAttributes());
        $this->assertDeleted($vietnamSourceLog);
    }

    public function test_destroy_soft()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->create();
        $attributes = $vietnamSourceLog->getAttributes();

        $result = $this->service->destroy($vietnamSourceLog->getKey());

        $this->assertTrue($result);
        $this->assertDatabaseHas('vietnam_source_logs', $attributes);
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($vietnamSourceLog);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_restore()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->withDeleted()->create();
        $attributes = $vietnamSourceLog->getAttributes();
        $vietnamSourceLogId = $vietnamSourceLog->getKey();
        $vietnamSourceLog->delete();

        $result = $this->service->restore($vietnamSourceLogId);

        $this->assertTrue($result);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_find_by()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->withDeleted()->create();

        $vietnamSourceLogFound = $this->service->findBy($vietnamSourceLog->getAttributes());

        $this->assertNotNull($vietnamSourceLogFound);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogFound);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogFound->getAttributes(), $vietnamSourceLogFound->getFillable()));
        $this->assertTrue($vietnamSourceLog->is($vietnamSourceLogFound));
        $this->assertEquals($vietnamSourceLog->getAttributes(), $vietnamSourceLogFound->getAttributes());
    }

    public function test_find_by_id()
    {
        $vietnamSourceLog = VietnamSourceLog::factory()->withDeleted()->create();

        $vietnamSourceLogFound = $this->service->findById($vietnamSourceLog->getKey());

        $this->assertNotNull($vietnamSourceLogFound);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLogFound);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLogFound->getAttributes(), $vietnamSourceLogFound->getFillable()));
        $this->assertTrue($vietnamSourceLog->is($vietnamSourceLogFound));
        $this->assertEquals($vietnamSourceLog->getAttributes(), $vietnamSourceLogFound->getAttributes());
    }

    public function test_first_or_create()
    {
        $vietnamSourceLogMake = VietnamSourceLog::factory()->make();
        $attributes = $vietnamSourceLogMake->getAttributes();

        $vietnamSourceLog = $this->service->firstOrCreate([], $attributes);

        $this->assertNotNull($vietnamSourceLog);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLog);
        $this->assertArraySubset(Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()), $attributes);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()));

    }

    public function test_first_or_create_with_data_already_exist()
    {
        $vietnamSourceLogNew = VietnamSourceLog::factory()->withDeleted()->create();
        $attributes = $vietnamSourceLogNew->getAttributes();

        $vietnamSourceLog = $this->service->firstOrCreate($attributes, $attributes);

        $this->assertNotNull($vietnamSourceLog);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLog);
        $this->assertTrue($vietnamSourceLogNew->is($vietnamSourceLog));
        $this->assertEquals($vietnamSourceLogNew->getAttributes(), $vietnamSourceLog->getAttributes());
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()));
    }

    public function test_update_or_create()
    {
        $vietnamSourceLogMake = VietnamSourceLog::factory()->make();
        $attributes = $vietnamSourceLogMake->getAttributes();

        $vietnamSourceLog = $this->service->updateOrCreate([], $attributes);

        $this->assertNotNull($vietnamSourceLog);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLog);
        $this->assertArraySubset(Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()), $attributes);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()));

    }

    public function test_update_or_create_with_data_already_exist()
    {
        $vietnamSourceLogNew = VietnamSourceLog::factory()->withDeleted()->create();
        $attributes = $vietnamSourceLogNew->getAttributes();

        $vietnamSourceLog = $this->service->updateOrCreate($attributes, $attributes);

        $this->assertNotNull($vietnamSourceLog);
        $this->assertInstanceOf(VietnamSourceLog::class, $vietnamSourceLog);
        $this->assertTrue($vietnamSourceLogNew->is($vietnamSourceLog));
        $this->assertEquals($vietnamSourceLogNew->getAttributes(), $vietnamSourceLog->getAttributes());
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourceLog->getAttributes(), $vietnamSourceLog->getFillable()));
    }

    public function test_export()
    {
        VietnamSourceLog::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'vietnam-source-log';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), VietnamSourceRequestExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        VietnamSourceLog::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'vietnam-source-log';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), VietnamSourceRequestExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
