<?php

namespace Tests\Unit\Services;

use App\Constants\MRP;
use App\Exports\MRPOrderingCalendarExport;
use App\Models\Admin;
use App\Models\MrpOrderCalendar;
use App\Models\MrpWeekDefinition;
use App\Models\OrderList;
use App\Models\Remark;
use App\Services\MrpOrderCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MrpOrderingCalendarTest extends TestCase
{
    use RefreshDatabase;

    const NUMBER_RECORD = 20;
    private $service;
    private $attrStore = [
        'contract_code',
        'part_group',
        'etd',
        'eta',
        'target_plan_from',
        'target_plan_to',
        'buffer_span_from',
        'buffer_span_to',
        'order_span_from',
        'order_span_to',
        'mrp_or_run',
        'remark'
    ];

    private $attrUpdate = [
        'etd',
        'eta',
        'target_plan_from',
        'target_plan_to',
        'buffer_span_from',
        'buffer_span_to',
        'order_span_from',
        'order_span_to',
        'mrp_or_run',
        'remark'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MrpOrderCalendarService();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_model()
    {
        $this->assertEquals(MrpOrderCalendar::class, $this->service->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_paginate()
    {
        $params = [
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];
        MrpOrderCalendar::factory()->count(self::NUMBER_RECORD)->create();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(MrpOrderCalendar::class, $this->service, $params);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);

    }

    public function test_paginate_has_search()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->create();
        $params = [
            'contract_code' => $mrpOrderCalendar['contract_code'],
            'part_group' => $mrpOrderCalendar['part_group'],
            'status' => $mrpOrderCalendar['status'],
            'page' => 1,
            'per_page' => self::NUMBER_RECORD
        ];

        $listItemQuery = MrpOrderCalendar::query()
            ->where('contract_code', 'LIKE', '%' . $mrpOrderCalendar['contract_code'] . '%')
            ->where('part_group', 'LIKE', '%' . $mrpOrderCalendar['part_group'] . '%')
            ->where('status', '=', $mrpOrderCalendar['status'])
            ->limit($params['per_page'])
            ->offset(0)
            ->latest('id')
            ->get();
        list($listItemService, $dataQuery, $dataService) = $this->getDataPaginate(MrpOrderCalendar::class, $this->service, $params, $listItemQuery);
        $this->assertInstanceOf(LengthAwarePaginator::class, $listItemService);
        $this->assertEquals($dataQuery, $dataService);
    }

    public function test_columns()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->count(self::NUMBER_RECORD)->create();
        $mrpOrderCalendarFillable = $mrpOrderCalendar->first()->getFillable();
        $column = Arr::random($mrpOrderCalendarFillable);

        request()->merge([
            'column' => $column,
            'keyword' => $mrpOrderCalendar->first()->getAttribute($column),
            'per_page' => self::NUMBER_RECORD,
        ]);
        $params = request()->toArray();

        $mrpOrderCalendarQuery = MrpOrderCalendar::query()
            ->where($column, 'LIKE', '%' . $params['keyword'] . '%')
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($params['per_page'])
            ->pluck($column)
            ->toArray();

        $result = $this->service->getColumnValue();

        $this->assertArraySubset($mrpOrderCalendarQuery, $result);
    }

    public function test_show()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->withDeleted()->create();
        $mrpOrderCalendarFound = $this->service->show($mrpOrderCalendar->getKey());
        $mrpOrderCalendarFound->setAttribute('etd', $mrpOrderCalendarFound->etd->format('Y-m-d'));
        $mrpOrderCalendarFound->setAttribute('eta', $mrpOrderCalendarFound->eta->format('Y-m-d'));
        $mrpOrderCalendarFound->setAttribute('mrp_or_run', $mrpOrderCalendarFound->mrp_or_run->format('Y-m-d'));

        $this->assertNotNull($mrpOrderCalendarFound);
        $this->assertInstanceOf(MrpOrderCalendar::class, $mrpOrderCalendarFound);
        $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarFound->getAttributes(), $mrpOrderCalendarFound->getFillable()));
        $this->assertTrue($mrpOrderCalendar->is($mrpOrderCalendarFound));
        $this->assertEquals($mrpOrderCalendar->getAttributes(), $mrpOrderCalendarFound->getAttributes());
    }

    public function test_show_with_trashes()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->withDeleted()->create();
        $mrpOrderCalendarId = $mrpOrderCalendar->getKey();
        $mrpOrderCalendar->delete();
        $mrpOrderCalendarFoundWithTrash = $this->service->show($mrpOrderCalendarId, [], [], [], true);
        $mrpOrderCalendarFoundWithTrash->setAttribute('etd', $mrpOrderCalendarFoundWithTrash->etd->format('Y-m-d'));
        $mrpOrderCalendarFoundWithTrash->setAttribute('eta', $mrpOrderCalendarFoundWithTrash->eta->format('Y-m-d'));
        $mrpOrderCalendarFoundWithTrash->setAttribute('mrp_or_run', $mrpOrderCalendarFoundWithTrash->mrp_or_run->format('Y-m-d'));

        $this->assertNotNull($mrpOrderCalendarFoundWithTrash);
        $this->assertInstanceOf(MrpOrderCalendar::class, $mrpOrderCalendarFoundWithTrash);
        $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarFoundWithTrash->getAttributes(), $mrpOrderCalendarFoundWithTrash->getFillable()));
        $this->assertTrue($mrpOrderCalendar->is($mrpOrderCalendarFoundWithTrash));
        $this->assertEquals($mrpOrderCalendar->getAttributes(), $mrpOrderCalendarFoundWithTrash->getAttributes());
        $this->assertSoftDeleted($mrpOrderCalendar);
    }

    public function test_show_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->show(1);
    }


    public function test_store()
    {
        $now = CarbonImmutable::now();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->format('Y-m-d')
        ])->create();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->addDays()->format('Y-m-d')
        ])->create();

        $mrpOrderCalendar = MrpOrderCalendar::factory()->make();

        $data = Arr::only($mrpOrderCalendar->getAttributes(), $this->attrStore);
        $mrpOrderCalendarConvert = $this->service->convertPayload($data);
        $mrpOrderCalendarCreated = $this->service->store($mrpOrderCalendarConvert);
        $this->assertInstanceOf(MrpOrderCalendar::class, $mrpOrderCalendarCreated);
        $this->assertArraySubset(Arr::only($mrpOrderCalendarConvert, $mrpOrderCalendar->getFillable()), $mrpOrderCalendarCreated->getAttributes());
        $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarConvert, $mrpOrderCalendar->getFillable()));
    }

    public function test_store_with_remark()
    {
        $now = CarbonImmutable::now();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->format('Y-m-d')
        ])->create();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->addDays()->format('Y-m-d')
        ])->create();
        $mrpOrderCalendar = MrpOrderCalendar::factory()->make();
        $data = Arr::only($mrpOrderCalendar->getAttributes(), $this->attrStore);

        $remark = Remark::factory()->forModel($mrpOrderCalendar)->make();
        request()->merge(['remark' => $remark->content]);
        $mrpOrderCalendarConvert = $this->service->convertPayload($data);
        $mrpOrderCalendarCreated = $this->service->store($mrpOrderCalendarConvert);
        $remarkCreated = $mrpOrderCalendarCreated->remarkable()->first();

        $this->assertInstanceOf(MrpOrderCalendar::class, $mrpOrderCalendarCreated);
        $this->assertInstanceOf(Remark::class, $remarkCreated);
        $this->assertArraySubset(Arr::only($mrpOrderCalendarConvert, $mrpOrderCalendar->getFillable()), $mrpOrderCalendarCreated->getAttributes());
        $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarConvert, $mrpOrderCalendar->getFillable()));
        $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
    }

    public function test_update()
    {
        $now = CarbonImmutable::now();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->format('Y-m-d')
        ])->create();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->addDays()->format('Y-m-d')
        ])->create();

        $makeOrigin = MrpOrderCalendar::factory()->make();
        $data = Arr::only($makeOrigin->getAttributes(), $this->attrStore);
        $payloadOrigin = $this->service->convertPayload($data);
        $mrpOrderCalendarOrigin = MrpOrderCalendar::factory()->sequence($payloadOrigin)->create();
        if ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
            $mrpOrderCalendarNew = MrpOrderCalendar::factory()->sequence([
                'eta' => Arr::random([$now->addDays(2)->format('Y-m-d'), null])
            ])->make();
        } else {
            $mrpOrderCalendarNew = MrpOrderCalendar::factory()->sequence([
                'eta' => $now->addDays(2)->format('Y-m-d')
            ])->make();
        }

        $mrpOrderCalendarNewAttributes = $mrpOrderCalendarNew->getAttributes();
        $data = Arr::add(Arr::only($mrpOrderCalendarNewAttributes, $this->attrUpdate), 'remark', 'remark');
        if ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
            $oderList = OrderList::factory()->sequence([
                'contract_code' => $mrpOrderCalendarOrigin['contract_code'],
                'part_group' => $mrpOrderCalendarOrigin['part_group']
            ])->create();
        }

        $validateResult = $this->service->validateETA($mrpOrderCalendarOrigin, $data);
        if (!$validateResult) {
            if ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_WAIT) {
                $payload = $this->service->convertPayload($data);
                $mrpOrderCalendarUpdated = $this->service->update($mrpOrderCalendarOrigin->getKey(), $payload);
            } elseif ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
                $mrpOrderCalendarUpdated = $this->service->updateEtaWhenStatusDone($mrpOrderCalendarOrigin->getKey(), $data);
                if ($data['eta']) {
                    $oderListUpdate = OrderList::find($oderList->getKey());
                    $this->assertEquals($oderListUpdate->eta, $mrpOrderCalendarUpdated->eta->format('Y-m-d'));
                }
            }
            $mrpOrderCalendarUpdated->setAttribute('etd', $mrpOrderCalendarUpdated->etd->format('Y-m-d'));
            $mrpOrderCalendarUpdated->setAttribute('eta', $mrpOrderCalendarUpdated->eta->format('Y-m-d'));
            $mrpOrderCalendarUpdated->setAttribute('mrp_or_run', $mrpOrderCalendarUpdated->mrp_or_run->format('Y-m-d'));
            $mrpOrderCalendarUpdatedAttributes = $mrpOrderCalendarUpdated->getAttributes();
            $this->assertInstanceOf(MrpOrderCalendar::class, $mrpOrderCalendarUpdated);
            $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarUpdatedAttributes, $mrpOrderCalendarUpdated->getFillable()));
            if ($data['eta']) {
                $this->assertDatabaseMissing('mrp_order_calendars', Arr::only($mrpOrderCalendarOrigin->getAttributes(), $mrpOrderCalendarOrigin->getFillable()));
            } else {
                $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarOrigin->getAttributes(), $mrpOrderCalendarOrigin->getFillable()));
            }
        }
    }

    public function test_update_with_remark()
    {
        $now = CarbonImmutable::now();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->format('Y-m-d')
        ])->create();
        MrpWeekDefinition::factory()->sequence([
            'date' => $now->addDays()->format('Y-m-d')
        ])->create();

        $makeOrigin = MrpOrderCalendar::factory()->make();
        $data = Arr::only($makeOrigin->getAttributes(), $this->attrStore);
        $payloadOrigin = $this->service->convertPayload($data);
        $mrpOrderCalendarOrigin = MrpOrderCalendar::factory()->sequence($payloadOrigin)->create();
        if ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
            $mrpOrderCalendarNew = MrpOrderCalendar::factory()->sequence([
                'eta' => Arr::random([$now->addDays(2)->format('Y-m-d'), null])
            ])->make();
        } else {
            $mrpOrderCalendarNew = MrpOrderCalendar::factory()->sequence([
                'eta' => $now->addDays(2)->format('Y-m-d')
            ])->make();
        }
        $remark = Remark::factory()->forModel($mrpOrderCalendarOrigin)->make();
        $mrpOrderCalendarNewAttributes = $mrpOrderCalendarNew->getAttributes();
        $data = Arr::add(Arr::only($mrpOrderCalendarNewAttributes, $this->attrUpdate), 'remark', 'remark');
        request()->merge(['remark' => $remark->content]);
        if ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
            $oderList = OrderList::factory()->sequence([
                'contract_code' => $mrpOrderCalendarOrigin['contract_code'],
                'part_group' => $mrpOrderCalendarOrigin['part_group']
            ])->create();
        }

        $validateResult = $this->service->validateETA($mrpOrderCalendarOrigin, $data);
        if (!$validateResult) {
            if ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_WAIT) {
                $payload = $this->service->convertPayload($data);
                $mrpOrderCalendarUpdated = $this->service->update($mrpOrderCalendarOrigin->getKey(), $payload);
            } elseif ($mrpOrderCalendarOrigin->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
                $mrpOrderCalendarUpdated = $this->service->updateEtaWhenStatusDone($mrpOrderCalendarOrigin->getKey(), $data);
                if ($data['eta']) {
                    $oderListUpdate = OrderList::find($oderList->getKey());
                    $this->assertEquals($oderListUpdate->eta, $mrpOrderCalendarUpdated->eta->format('Y-m-d'));
                }
            }
            $mrpOrderCalendarUpdated->setAttribute('etd', $mrpOrderCalendarUpdated->etd->format('Y-m-d'));
            $mrpOrderCalendarUpdated->setAttribute('eta', $mrpOrderCalendarUpdated->eta->format('Y-m-d'));
            $mrpOrderCalendarUpdated->setAttribute('mrp_or_run', $mrpOrderCalendarUpdated->mrp_or_run->format('Y-m-d'));
            $mrpOrderCalendarUpdatedAttributes = $mrpOrderCalendarUpdated->getAttributes();
            $remarkCreated = $mrpOrderCalendarUpdated->remarkable()->first();
            $this->assertInstanceOf(Remark::class, $remarkCreated);
            $this->assertDatabaseHas('remarks', Arr::only($remarkCreated->getAttributes(), $remarkCreated->getFillable()));
            $this->assertInstanceOf(MrpOrderCalendar::class, $mrpOrderCalendarUpdated);
            $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarUpdatedAttributes, $mrpOrderCalendarUpdated->getFillable()));
            if ($data['eta']) {
                $this->assertDatabaseMissing('mrp_order_calendars', Arr::only($mrpOrderCalendarOrigin->getAttributes(), $mrpOrderCalendarOrigin->getFillable()));
            } else {
                $this->assertDatabaseHas('mrp_order_calendars', Arr::only($mrpOrderCalendarOrigin->getAttributes(), $mrpOrderCalendarOrigin->getFillable()));
            }
        }
    }

    public function test_update_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->update(1, []);
    }

    public function test_destroy_soft()
    {
        $mrpOrderCalendar = MrpOrderCalendar::factory()->create();
        $attributes = $mrpOrderCalendar->getAttributes();
        $result = $this->service->destroy($mrpOrderCalendar->getKey());
        $this->assertTrue($result);
        $this->assertDatabaseHas('mrp_order_calendars', $attributes);
        $this->assertDatabaseMissing('mrp_order_calendars', Arr::set($attributes, 'deleted_at', null));
        $this->assertSoftDeleted($mrpOrderCalendar);
    }

    public function test_destroy_id_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->destroy(1);
    }

    public function test_export()
    {
        MrpOrderCalendar::factory()->count(5)->create();
        $type = 'xlsx';
        $fileName = 'mrp-order-calendar';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), MRPOrderingCalendarExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == $type);
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }

    public function test_export_type_pdf()
    {
        MrpOrderCalendar::factory()->count(5)->create();
        $type = 'pdf';
        request()->merge(['type' => $type]);
        $fileName = 'mrp-order-calendar';
        $dateFile = now()->format('dmY');

        $response = $this->service->export(request(), MRPOrderingCalendarExport::class, $fileName);

        $this->assertNotNull($response->getFile()->getContent());
        $this->assertTrue($response->getFile()->getExtension() == strtolower(\Maatwebsite\Excel\Excel::MPDF));
        $this->assertTrue($response->headers->get('content-disposition') == ('attachment; filename=' . $fileName . '_' . $dateFile . '.' . $type));
    }
}
