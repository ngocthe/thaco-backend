<?php

namespace Tests\Unit\Services;

use App\Exports\InTransitInventoryLogExport;
use App\Models\Admin;
use App\Models\BoxType;
use App\Models\BwhInventoryLog;
use App\Models\InTransitInventoryLog;
use App\Models\Remark;
use App\Services\InTransitInventoryLogService;
use Faker\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class InTransitInventoryLogTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new InTransitInventoryLogService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(InTransitInventoryLog::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => 20
        ];
        InTransitInventoryLog::factory()->count(20)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(InTransitInventoryLog::class, $this->service, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);

    }

    public function test_paginate_has_search()
    {
        $date = now();
        $paramSearch = [
            'contract_code' => 'VN-2107JM',
            'invoice_code' => 'VN-2107JM',
            'bill_of_lading_code' => 'SKYJE-9336871',
            'container_code' => 'TCNU2343940',
            'case_code' => '376-RA-01',
            'part_code' => 'BWLS55100',
            'part_color_code' => '02',
            'plant_code' => 'TLUX',
            'supplier_code' => 'MCJPN',
            'container_shipped' => date("Y-m-d"),
            'updated_at' => $date,
            'page' => 1,
            'per_page' => 20
        ];

        InTransitInventoryLog::factory()
            ->sequence([
                'contract_code' => $paramSearch['contract_code'],
                'invoice_code' => $paramSearch['invoice_code'],
                'bill_of_lading_code' => $paramSearch['bill_of_lading_code'],
                'container_code' => $paramSearch['container_code'],
                'case_code' => $paramSearch['case_code'],
                'part_code' => $paramSearch['part_code'],
                'part_color_code' => $paramSearch['part_color_code'],
                'plant_code' => $paramSearch['plant_code'],
                'supplier_code' => $paramSearch['supplier_code'],
                'container_shipped' => $paramSearch['container_shipped'],
                'updated_at' => $paramSearch['updated_at'],
            ])
            ->create();

        $listItemQuery = InTransitInventoryLog::query()
            ->where('contract_code', 'LIKE', '%' . $paramSearch['contract_code'] . '%')
            ->where('invoice_code', 'LIKE', '%' . $paramSearch['invoice_code'] . '%')
            ->where('bill_of_lading_code', 'LIKE', '%' . $paramSearch['bill_of_lading_code'] . '%')
            ->where('container_code', 'LIKE', '%' . $paramSearch['container_code'] . '%')
            ->where('case_code', 'LIKE', '%' . $paramSearch['case_code'] . '%')
            ->where('supplier_code', 'LIKE', '%' . $paramSearch['supplier_code'] . '%')
            ->where('part_code', 'LIKE', '%' . $paramSearch['part_code'] . '%')
            ->where('part_color_code', 'LIKE', '%' . $paramSearch['part_color_code'] . '%')
            ->where('plant_code', 'LIKE', '%' . $paramSearch['plant_code'] . '%')
            ->where('container_shipped', $paramSearch['container_shipped'])
            ->whereDate('updated_at', '=', $paramSearch['updated_at'])
            ->limit($paramSearch['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(InTransitInventoryLog::class, $this->service, $paramSearch, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_show()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->withDeleted()->create();
        $inTransitInventoryLogFound = $this->service->show($inTransitInventoryLog->getKey());
        $inTransitInventoryLogFound->setAttribute('etd', $inTransitInventoryLog->etd->format('Y-m-d'));
        $inTransitInventoryLogFound->setAttribute('container_shipped', $inTransitInventoryLog->container_shipped->format('Y-m-d'));
        $inTransitInventoryLogFound->setAttribute('eta', $inTransitInventoryLog->eta->format('Y-m-d'));

        $this->assertNotNull($inTransitInventoryLogFound);
        $this->assertInstanceOf(InTransitInventoryLog::class, $inTransitInventoryLogFound);
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryLogFound->getAttributes(), $inTransitInventoryLogFound->getFillable()));
        $this->assertTrue($inTransitInventoryLog->is($inTransitInventoryLogFound));
        $this->assertEquals($inTransitInventoryLog->getAttributes(), $inTransitInventoryLogFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->withDeleted()->create();
        $inTransitInventoryLogId = $inTransitInventoryLog->getKey();
        $inTransitInventoryLog->delete();
        $inTransitInventoryFoundWithTrash = $this->service->show($inTransitInventoryLogId, [], [], [], true);
        $inTransitInventoryFoundWithTrash->setAttribute('etd', $inTransitInventoryFoundWithTrash->etd->format('Y-m-d'));
        $inTransitInventoryFoundWithTrash->setAttribute('container_shipped', $inTransitInventoryFoundWithTrash->container_shipped->format('Y-m-d'));
        $inTransitInventoryFoundWithTrash->setAttribute('eta', $inTransitInventoryFoundWithTrash->eta->format('Y-m-d'));

        $this->assertNotNull($inTransitInventoryFoundWithTrash);
        $this->assertInstanceOf(InTransitInventoryLog::class, $inTransitInventoryFoundWithTrash);
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryFoundWithTrash->getAttributes(), $inTransitInventoryFoundWithTrash->getFillable()));
        $this->assertTrue($inTransitInventoryLog->is($inTransitInventoryFoundWithTrash));
        $this->assertEquals($inTransitInventoryLog->getAttributes(), $inTransitInventoryFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($inTransitInventoryLog);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }

    public function test_store()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $faker = Factory::create();
        BoxType::query()
            ->firstOrCreate([
                'code' => $inTransitInventoryLog->toArray()['box_type_code'],
                'part_code' => $inTransitInventoryLog->toArray()['part_code'],
                'description' => $faker->text,
                'weight' => rand(1, 10),
                'width' => rand(1, 10),
                'height' => rand(1, 10),
                'depth' => rand(1, 10),
                'quantity' => $inTransitInventoryLog->toArray()['part_quantity'],
                'unit' => $inTransitInventoryLog->toArray()['unit'],
                'plant_code' => $inTransitInventoryLog->toArray()['plant_code'],
            ]);
        $data = Arr::only($inTransitInventoryLog->getAttributes(), [
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
            'etd',
            'container_shipped',
            'eta',
            'plant_code'
        ]);

        $inTransitInventoryLogCreated = $this->service->store($data);

        $this->assertInstanceOf(InTransitInventoryLog::class, $inTransitInventoryLogCreated);
        $this->assertArraySubset(Arr::only($inTransitInventoryLog->getAttributes(), $inTransitInventoryLog->getFillable()), $inTransitInventoryLogCreated->getAttributes());
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryLogCreated->getAttributes(), $inTransitInventoryLogCreated->getFillable()));
    }

    public function test_store_with_remark()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $faker = Factory::create();
        $remark = Remark::factory()->forModel($inTransitInventoryLog)->make();
        BoxType::query()
            ->firstOrCreate([
                'code' => $inTransitInventoryLog->toArray()['box_type_code'],
                'part_code' => $inTransitInventoryLog->toArray()['part_code'],
                'description' => $faker->text,
                'weight' => rand(1, 10),
                'width' => rand(1, 10),
                'height' => rand(1, 10),
                'depth' => rand(1, 10),
                'quantity' => $inTransitInventoryLog->toArray()['part_quantity'],
                'unit' => $inTransitInventoryLog->toArray()['unit'],
                'plant_code' => $inTransitInventoryLog->toArray()['plant_code'],
            ]);
        $data = Arr::only($inTransitInventoryLog->getAttributes(), [
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
            'etd',
            'container_shipped',
            'eta',
            'plant_code'
        ]);
        request()->merge(['remark' => $remark->content]);
        $inTransitInventoryLogCreated = $this->service->store($data);
        $remarkCreated = $inTransitInventoryLogCreated->remarkable()->first();

        $this->assertInstanceOf(InTransitInventoryLog::class, $inTransitInventoryLogCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($inTransitInventoryLog->getAttributes(), $inTransitInventoryLog->getFillable()), $inTransitInventoryLogCreated->getAttributes());
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryLogCreated->getAttributes(), $inTransitInventoryLogCreated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $inTransitInventoryLogOrigin = InTransitInventoryLog::factory()->create();
        $inTransitInventoryLogNew = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogNewAttributes = $inTransitInventoryLogNew->getAttributes();

        $data = Arr::add(Arr::only($inTransitInventoryLogNewAttributes, [
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
            'etd',
            'container_shipped',
            'eta',
            'plant_code'
        ]), 'remark', 'remark');

        $inTransitInventoryUpdated = $this->service->update($inTransitInventoryLogOrigin->getKey(), $data);
        $inTransitInventoryUpdated->setAttribute('etd', $inTransitInventoryUpdated->etd->format('Y-m-d'));
        $inTransitInventoryUpdated->setAttribute('container_shipped', $inTransitInventoryUpdated->container_shipped->format('Y-m-d'));
        $inTransitInventoryUpdated->setAttribute('eta', $inTransitInventoryUpdated->eta->format('Y-m-d'));
        $inTransitInventoryUpdatedAttributes = $inTransitInventoryUpdated->getAttributes();

        $this->assertInstanceOf(InTransitInventoryLog::class, $inTransitInventoryUpdated);
        $this->assertArraySubset(Arr::only($inTransitInventoryLogNewAttributes, $inTransitInventoryLogNew->getFillable()), $inTransitInventoryUpdatedAttributes);
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryUpdatedAttributes, $inTransitInventoryUpdated->getFillable()));
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogOrigin->getAttributes(), $inTransitInventoryLogOrigin->getFillable()));
    }

    public function test_update_with_remark()
    {
        $inTransitInventoryLogOrigin = InTransitInventoryLog::factory()->create();
        $inTransitInventoryLogNew = InTransitInventoryLog::factory()->make();
        $remark = Remark::factory()->forModel($inTransitInventoryLogNew)->make();
        $inTransitInventoryLogNewAttributes = $inTransitInventoryLogNew->getAttributes();

        $data = Arr::add(Arr::only($inTransitInventoryLogNewAttributes, [
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
            'etd',
            'container_shipped',
            'eta',
            'plant_code'
        ]), 'remark', 'remark');

        request()->merge(['remark' => $remark->content]);
        $inTransitInventoryUpdated = $this->service->update($inTransitInventoryLogOrigin->getKey(), $data);
        $inTransitInventoryUpdated->setAttribute('etd', $inTransitInventoryUpdated->etd->format('Y-m-d'));
        $inTransitInventoryUpdated->setAttribute('container_shipped', $inTransitInventoryUpdated->container_shipped->format('Y-m-d'));
        $inTransitInventoryUpdated->setAttribute('eta', $inTransitInventoryUpdated->eta->format('Y-m-d'));
        $inTransitInventoryUpdatedAttributes = $inTransitInventoryUpdated->getAttributes();
        $remarkCreated = $inTransitInventoryUpdated->remarkable()->first();

        $this->assertInstanceOf(InTransitInventoryLog::class, $inTransitInventoryUpdated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($inTransitInventoryLogNewAttributes, $inTransitInventoryLogNew->getFillable()), $inTransitInventoryUpdatedAttributes);
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryUpdatedAttributes, $inTransitInventoryUpdated->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogOrigin->getAttributes(), $inTransitInventoryLogOrigin->getFillable()));
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_soft()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->create();

        $result = $this->service->destroy($inTransitInventoryLog->getKey(), true);

        $this->assertTrue($result);
        $attributes = $inTransitInventoryLog->getAttributes();
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($inTransitInventoryLog);
    }

    public function test_destroy_with_class_relation_delete_already_exist()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->create();
        $bwhInventoryLog = BwhInventoryLog::query()->firstOrCreate([
            'contract_code' => $inTransitInventoryLog->contract_code,
            'invoice_code' => $inTransitInventoryLog->invoice_code,
            'bill_of_lading_code' => $inTransitInventoryLog->bill_of_lading_code,
            'container_code' => $inTransitInventoryLog->container_code,
            'case_code' => $inTransitInventoryLog->case_code,
            'plant_code' => $inTransitInventoryLog->plant_code,
            'part_code' => Str::random(10),
            'part_color_code' => (string)(rand(1, 20)),
            'box_type_code' => Str::random(5),
            'box_quantity' => rand(1, 100),
            'part_quantity' => rand(1, 100),
            'unit' => Str::random(6),
            'supplier_code' => Str::random(5),
            'container_received' => date("Y-m-d"),
            'devanned_date' => date("Y-m-d"),
            'stored_date' => date("Y-m-d"),
            'warehouse_location_code' => Str::random(8),
            'warehouse_code' => Str::random(8),
            'shipped_date' => date("Y-m-d"),
            'defect_id' => Str::random(2),
        ]);
        $result = $this->service->destroy($inTransitInventoryLog->getKey());

        $this->assertFalse($result);
        $this->assertDatabaseHas('in_transit_inventory_logs', $inTransitInventoryLog->getAttributes());
        $this->assertDatabaseHas('bwh_inventory_logs', $bwhInventoryLog->getAttributes());
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_export()
    {
        InTransitInventoryLog::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'in-transit-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), InTransitInventoryLogExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        InTransitInventoryLog::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'in-transit-inventory';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), InTransitInventoryLogExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
