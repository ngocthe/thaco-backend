<?php

namespace Tests\Unit\Services;

use App\Constants\MRP;
use App\Exports\BwhInventoryLogExport;
use App\Exports\TemplateExport;
use App\Imports\BoxTypeImport;
use App\Imports\BwhInventoryLogImport;
use App\Models\Admin;
use App\Models\BwhInventoryLog;
use App\Models\InTransitInventoryLog;
use App\Models\OrderList;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Models\WarehouseLocation;
use App\Services\DataInventoryImportService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BwhInventoryLogImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $typeImportClass;
    private $dataContent;
    private $headingsClass;
    private $exportTitle;
    private $uniqueKeys;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new DataInventoryImportService();
        $this->typeImportClass = 'bwh_inventory_log';
        $this->headingsClass = BwhInventoryLogImport::HEADING_ROW;
        $this->exportTitle = BwhInventoryLogExport::TITLE;
        $this->dataContent = [
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'container_received',
            'devanned_date',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'plant_code',
            'defect_id'
        ];
        $this->uniqueKeys = [
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'plant_code'
        ];
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function tearDown(): void
    {
        Storage::disk('public')->deleteDirectory('testing');
        parent::tearDown();
    }

    public function test_bwh_inventory_log_import_insert()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();
        //Find inTransitInventoryLog by uniqueData and change attributes for insert bwhInventoryLog
        $bwhInventoryLogAttributes = array_merge($bwhInventoryLogAttributes, Arr::only($inTransitInventoryLogAttributes, [
            'part_code',
            'part_color_code',
            'box_type_code',
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'is_parent_case'
        ]));

        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'contract_code' => $bwhInventoryLogAttributes['contract_code'],
            'status' => MRP::MRP_ORDER_LIST_STATUS_RELEASE
        ])->create();
        $orderListAttributes = $orderList->getAttributes();

        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only($orderListAttributes, $orderList->getFillable()));
        //update status order list
        $this->assertDatabaseHas('order_lists', Arr::only(Arr::set($orderListUpdateAttributes, 'status', MRP::MRP_ORDER_LIST_STATUS_DONE), $orderList->getFillable()));
    }

    public function test_bwh_inventory_log_import_update()
    {
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()->create();
        $bwhInventoryLogOriginAttributes = $bwhInventoryLogOrigin->getAttributes();

        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence(fn($sequence) => Arr::only($bwhInventoryLogOriginAttributes, $this->uniqueKeys) +
                [
                    'warehouse_code' => $bwhInventoryLogOriginAttributes['warehouse_code'],
                    'shipped_date' => $bwhInventoryLogOriginAttributes['shipped_date'],
                    'part_code' => $bwhInventoryLogOriginAttributes['part_code'],
                    'part_color_code' => $bwhInventoryLogOriginAttributes['part_color_code'],
                    'box_type_code' => $bwhInventoryLogOriginAttributes['box_type_code'],
                    'box_quantity' => $bwhInventoryLogOriginAttributes['box_quantity'],
                    'part_quantity' => $bwhInventoryLogOriginAttributes['part_quantity'],
                    'unit' => $bwhInventoryLogOriginAttributes['unit'],
                    'supplier_code' => $bwhInventoryLogOriginAttributes['supplier_code']
                ]
            )
            ->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $this->createCorrectData($bwhInventoryLogAttributes);

        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'contract_code' => $bwhInventoryLogAttributes['contract_code'],
            'status' => MRP::MRP_ORDER_LIST_STATUS_RELEASE
        ])->create();
        $orderListAttributes = $orderList->getAttributes();


        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOriginAttributes, $bwhInventoryLogOrigin->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only($orderListAttributes, $orderList->getFillable()));
        //update status order list
        $this->assertDatabaseHas('order_lists', Arr::only(Arr::set($orderListUpdateAttributes, 'status', MRP::MRP_ORDER_LIST_STATUS_DONE), $orderList->getFillable()));
    }

    public function test_bwh_inventory_log_import_update_new_warehouse_and_not_defect_insert_warehouse_inventory_summary()
    {
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'warehouse_code' => null,
            'defect_id' => null
        ])->create();
        $bwhInventoryLogOriginAttributes = $bwhInventoryLogOrigin->getAttributes();

        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence(fn($sequence) => Arr::only($bwhInventoryLogOriginAttributes, $this->uniqueKeys) +
                [
                    'shipped_date' => $bwhInventoryLogOriginAttributes['shipped_date'],
                    'part_code' => $bwhInventoryLogOriginAttributes['part_code'],
                    'part_color_code' => $bwhInventoryLogOriginAttributes['part_color_code'],
                    'box_type_code' => $bwhInventoryLogOriginAttributes['box_type_code'],
                    'box_quantity' => $bwhInventoryLogOriginAttributes['box_quantity'],
                    'part_quantity' => $bwhInventoryLogOriginAttributes['part_quantity'],
                    'unit' => $bwhInventoryLogOriginAttributes['unit'],
                    'supplier_code' => $bwhInventoryLogOriginAttributes['supplier_code'],
                    'defect_id' => null
                ]
            )
            ->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $quantity = 1 * $inTransitInventoryLogAttributes['box_quantity'] * $inTransitInventoryLogAttributes['part_quantity'];

        $warehouseInventorySummary = WarehouseInventorySummary::factory()->sequence(fn($sequence) => [
            'part_code' => $inTransitInventoryLogAttributes['part_code'],
            'part_color_code' => $inTransitInventoryLogAttributes['part_color_code'],
            'warehouse_code' => $bwhInventoryLogAttributes['warehouse_code'],
            'warehouse_type' => WarehouseInventorySummary::TYPE_BWH,
            'unit' => $inTransitInventoryLogAttributes['unit'],
            'plant_code' => $inTransitInventoryLogAttributes['plant_code'],
            'quantity' => $quantity
        ])->make();

        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'contract_code' => $bwhInventoryLogAttributes['contract_code'],
            'status' => MRP::MRP_ORDER_LIST_STATUS_RELEASE
        ])->create();
        $orderListAttributes = $orderList->getAttributes();


        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOriginAttributes, $bwhInventoryLogOrigin->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only($orderListAttributes, $orderList->getFillable()));
        //update status order list
        $this->assertDatabaseHas('order_lists', Arr::only(Arr::set($orderListUpdateAttributes, 'status', MRP::MRP_ORDER_LIST_STATUS_DONE), $orderList->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
    }

    public function test_bwh_inventory_log_import_update_new_warehouse_and_not_defect_update_warehouse_inventory_summary()
    {
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'warehouse_code' => null,
            'defect_id' => null
        ])->create();
        $bwhInventoryLogOriginAttributes = $bwhInventoryLogOrigin->getAttributes();

        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence(fn($sequence) => Arr::only($bwhInventoryLogOriginAttributes, $this->uniqueKeys) +
                [
                    'shipped_date' => $bwhInventoryLogOriginAttributes['shipped_date'],
                    'part_code' => $bwhInventoryLogOriginAttributes['part_code'],
                    'part_color_code' => $bwhInventoryLogOriginAttributes['part_color_code'],
                    'box_type_code' => $bwhInventoryLogOriginAttributes['box_type_code'],
                    'box_quantity' => $bwhInventoryLogOriginAttributes['box_quantity'],
                    'part_quantity' => $bwhInventoryLogOriginAttributes['part_quantity'],
                    'unit' => $bwhInventoryLogOriginAttributes['unit'],
                    'supplier_code' => $bwhInventoryLogOriginAttributes['supplier_code'],
                    'defect_id' => null
                ]
            )
            ->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $warehouseInventorySummaryOrigin = WarehouseInventorySummary::factory()->sequence(fn($sequence) => [
            'part_code' => $inTransitInventoryLogAttributes['part_code'],
            'part_color_code' => $inTransitInventoryLogAttributes['part_color_code'],
            'warehouse_code' => $bwhInventoryLogAttributes['warehouse_code'],
            'warehouse_type' => WarehouseInventorySummary::TYPE_BWH,
            'plant_code' => $inTransitInventoryLogAttributes['plant_code']
        ])->create();
        $warehouseInventorySummaryOriginAttributes = $warehouseInventorySummaryOrigin->getAttributes();

        $quantity = 1 * $inTransitInventoryLogAttributes['box_quantity'] * $inTransitInventoryLogAttributes['part_quantity'];

        $warehouseInventorySummary = WarehouseInventorySummary::factory()->sequence(fn($sequence) => [
            'part_code' => $warehouseInventorySummaryOriginAttributes['part_code'],
            'part_color_code' => $warehouseInventorySummaryOriginAttributes['part_color_code'],
            'warehouse_code' => $warehouseInventorySummaryOriginAttributes['warehouse_code'],
            'warehouse_type' => WarehouseInventorySummary::TYPE_BWH,
            'unit' => $warehouseInventorySummaryOriginAttributes['unit'],
            'plant_code' => $warehouseInventorySummaryOriginAttributes['plant_code'],
            'quantity' => $warehouseInventorySummaryOriginAttributes['quantity'] + $quantity
        ])->make();

        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'contract_code' => $bwhInventoryLogAttributes['contract_code'],
            'status' => MRP::MRP_ORDER_LIST_STATUS_RELEASE
        ])->create();
        $orderListAttributes = $orderList->getAttributes();


        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogOriginAttributes, $bwhInventoryLogOrigin->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only($orderListAttributes, $orderList->getFillable()));
        //update status order list
        $this->assertDatabaseHas('order_lists', Arr::only(Arr::set($orderListUpdateAttributes, 'status', MRP::MRP_ORDER_LIST_STATUS_DONE), $orderList->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
    }

    public function test_bwh_inventory_log_import_missing_data()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();
        //Find inTransitInventoryLog by uniqueData and change attributes for insert bwhInventoryLog
        $bwhInventoryLogAttributes = array_merge($bwhInventoryLogAttributes, Arr::only($inTransitInventoryLogAttributes, [
            'part_code',
            'part_color_code',
            'box_type_code',
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'is_parent_case'
        ]));

        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'contract_code' => $bwhInventoryLogAttributes['contract_code'],
            'status' => MRP::MRP_ORDER_LIST_STATUS_RELEASE
        ])->create();
        $orderListAttributes = $orderList->getAttributes();

        $fileNew = $this->createFileImport([], [], [
            [
                'contract_code' => null,
                'invoice_code' => null,
                'bill_of_lading_code' => null,
                'container_code' => null,
                'case_code' => null,
                'container_received' => null,
                'devanned_date' => null,
                'stored_date' => null,
                'warehouse_location_code' => null,
                'warehouse_code' => null,
                'shipped_date' => null,
                'plant_code' => null,
                'defect_id' => null
            ]
        ], true);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, '', 'The import file has missing data.', '', 4, false);
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
        //Not update order list
        $this->assertDatabaseHas('order_lists', Arr::only($orderListAttributes, $orderList->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only(Arr::set($orderListUpdateAttributes, 'status', MRP::MRP_ORDER_LIST_STATUS_DONE), $orderList->getFillable()));
    }

    public function test_bwh_inventory_log_import_update_with_fail_check_warehouse_code()
    {
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()->create();
        $bwhInventoryLogOriginAttributes = $bwhInventoryLogOrigin->getAttributes();

        //Make warehouse_code different
        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence(fn($sequence) => Arr::only($bwhInventoryLogOriginAttributes, $this->uniqueKeys)
                + ['warehouse_code' => strrev($bwhInventoryLogOriginAttributes['warehouse_code'])])
            ->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $this->createCorrectData($bwhInventoryLogAttributes);

        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Warehouse Code', 'The warehouse code is invalid, the container has been saved to another warehouse',
            $bwhInventoryLogAttributes['warehouse_code']);
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_update_with_fail_check_shipped_date()
    {
        $bwhInventoryLogOrigin = BwhInventoryLog::factory()->create();
        $bwhInventoryLogOriginAttributes = $bwhInventoryLogOrigin->getAttributes();

        //Make shipped_date different
        $bwhInventoryLog = BwhInventoryLog::factory()
            ->sequence(fn($sequence) => Arr::only($bwhInventoryLogOriginAttributes, $this->uniqueKeys) +
                [
                    'warehouse_code' => $bwhInventoryLogOriginAttributes['warehouse_code'],
                    'shipped_date' => Carbon::parse($bwhInventoryLogOriginAttributes['shipped_date'])->addDays(1)->format('Y-m-d')
                ]
            )
            ->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $this->createCorrectData($bwhInventoryLogAttributes);

        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Date Case shipped to UPKWH', 'The Date Case shipped to UPKWH is invalid, the container has been shipped',
            Carbon::parse($bwhInventoryLogAttributes['shipped_date'])->format('d/m/Y'));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_handle_duplicate_error()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();
        //Find inTransitInventoryLog by uniqueData and change attributes for insert bwhInventoryLog
        $bwhInventoryLogAttributes = array_merge($bwhInventoryLogAttributes, Arr::only($inTransitInventoryLogAttributes, [
            'part_code',
            'part_color_code',
            'box_type_code',
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'is_parent_case'
        ]));

        $orderList = OrderList::factory()->sequence(fn($sequence) => [
            'contract_code' => $bwhInventoryLogAttributes['contract_code'],
            'status' => MRP::MRP_ORDER_LIST_STATUS_RELEASE
        ])->create();
        $orderListAttributes = $orderList->getAttributes();

        $dataRow = Arr::only($bwhInventoryLogAttributes, $this->dataContent);
        $dataContent = [$dataRow, $dataRow];

        $fileNew = $this->createFileImport([], [], $dataContent);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $uniqueData = [];
        foreach ($this->uniqueKeys as $key) {
            $uniqueData[$key] = $dataRow[$key];
        }

        $this->checkAssertFailValidate($response,
            'Contract No., Invoice No., B/L No., Container No., Case No., Plant Code',
            'Duplicate data', implode(',', $uniqueData), 5);
        $this->assertDatabaseHas('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
        $this->assertDatabaseMissing('order_lists', Arr::only($orderListAttributes, $orderList->getFillable()));
        //update status order list
        $this->assertDatabaseHas('order_lists', Arr::only(Arr::set($orderListUpdateAttributes, 'status', MRP::MRP_ORDER_LIST_STATUS_DONE), $orderList->getFillable()));
    }

    public function test_bwh_inventory_log_import_heading_row_in_correct()
    {

        $bwhInventoryLog = BwhInventoryLog::factory()->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $this->createCorrectData($bwhInventoryLogAttributes);
        //Make wrong heading class
        $fileNew = $this->createFileImport($bwhInventoryLogAttributes, BoxTypeImport::HEADING_ROW);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArraySubset($response, [
            'rows' => [
                [
                    'line' => 3,
                    'attribute' => '',
                    'errors' => 'The import file has missing table column',
                    'value' => ''
                ]
            ],
            'link_download_error' => ''
        ]);
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_with_fail_check_warehouse()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes, false, false, true);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();
        //Find inTransitInventoryLog by uniqueData and change attributes for insert bwhInventoryLog
        $bwhInventoryLogAttributes = array_merge($bwhInventoryLogAttributes, Arr::only($inTransitInventoryLogAttributes, [
            'part_code',
            'part_color_code',
            'box_type_code',
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'is_parent_case'
        ]));
        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Warehouse Code, Plant Code', 'Warehouse Code, Plant Code are not linked together.',
            implode(', ', [$bwhInventoryLogAttributes['warehouse_code'], Warehouse::TYPE_BWH, $bwhInventoryLogAttributes['plant_code']]));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_with_fail_check_warehouse_location()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'warehouse_code' => null
        ])->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes, false, false, false, true);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();
        //Find inTransitInventoryLog by uniqueData and change attributes for insert bwhInventoryLog
        $bwhInventoryLogAttributes = array_merge($bwhInventoryLogAttributes, Arr::only($inTransitInventoryLogAttributes, [
            'part_code',
            'part_color_code',
            'box_type_code',
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'is_parent_case'
        ]));
        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Location Code, Plant Code', 'Location Code, Plant Code are not linked together.',
            implode(', ', [$bwhInventoryLogAttributes['warehouse_location_code'], $bwhInventoryLogAttributes['plant_code']]));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_with_fail_check_warehouse_and_warehouse_location()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $inTransitInventoryLog = $this->createCorrectData($bwhInventoryLogAttributes, true, false, false, true);
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();
        //Find inTransitInventoryLog by uniqueData and change attributes for insert bwhInventoryLog
        $bwhInventoryLogAttributes = array_merge($bwhInventoryLogAttributes, Arr::only($inTransitInventoryLogAttributes, [
            'part_code',
            'part_color_code',
            'box_type_code',
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'is_parent_case'
        ]));
        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Location Code, Warehouse Code, Plant Code', 'The Location Code, Warehouse Code, Plant Code does not exist.',
            implode(', ', [$bwhInventoryLogAttributes['warehouse_location_code'], $bwhInventoryLogAttributes['warehouse_code'], $bwhInventoryLogAttributes['plant_code']]));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_with_fail_check_in_transit_inventory_log()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();


        $this->createCorrectData($bwhInventoryLogAttributes, true, true, true, false);

        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Contract No., Invoice No., B/L No., Container No., Case No., Plant Code',
            'There is no corresponding data in the table in transit inventory',
            implode('-', Arr::only($bwhInventoryLogAttributes, $this->uniqueKeys)));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_with_fail_handle_after_date_error_container_received_and_devanned_date()
    {
        $now = CarbonImmutable::now();
        //Make devanned_date before container_received
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'container_received' => $now->addDays(1)->format('Y-m-d'),
            'devanned_date' => $now->format('Y-m-d'),
        ])->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $this->createCorrectData($bwhInventoryLogAttributes, true, true, true, true);

        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Date Container Devanned',
            'Date Container Devanned must come after or equal Date Container Received',
            Carbon::parse($bwhInventoryLogAttributes['devanned_date'])->format('d/m/Y'));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_with_fail_handle_after_date_error_devanned_date_and_stored_date()
    {
        $now = CarbonImmutable::now();
        //Make stored_date before devanned_date
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'devanned_date' => $now->addDays(1)->format('Y-m-d'),
            'stored_date' => $now->format('Y-m-d'),
        ])->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();

        $this->createCorrectData($bwhInventoryLogAttributes, true, true, true, true);

        $fileNew = $this->createFileImport($bwhInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Date Case Stored',
            'Date Case Stored must come after or equal Date Container Devanned',
            Carbon::parse($bwhInventoryLogAttributes['stored_date'])->format('d/m/Y'));
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    public function test_bwh_inventory_log_import_with_fail_rules_validate()
    {
        $bwhInventoryLog = BwhInventoryLog::factory()->sequence(fn($sequence) => [
            'contract_code' => null,
            'invoice_code' => null,
            'bill_of_lading_code' => null,
            'container_code' => null,
            'case_code' => null,
            'container_received' => null,
            'devanned_date' => null,
            'stored_date' => null,
            'warehouse_location_code' => strtoupper(Str::random(9)),
            'warehouse_code' => strtoupper(Str::random(9)),
            'plant_code' => null,
            'defect_id' => 'A'
        ])->make();
        $bwhInventoryLogAttributes = $bwhInventoryLog->getAttributes();
        $bwhInventoryLogAttributes['container_received'] = Str::random(8);
        $bwhInventoryLogAttributes['devanned_date'] = Str::random(8);
        $bwhInventoryLogAttributes['stored_date'] = Str::random(8);
        $bwhInventoryLogAttributes['shipped_date'] = Str::random(8);

        $fileNew = $this->createFileImport($bwhInventoryLogAttributes, [], [], true);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString(env('AWS_URL'), $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        foreach (BwhInventoryLogImport::MAP_HEADING_ROW as $key => $attribute) {
            if (in_array($key, ['container_received', 'devanned_date', 'stored_date', 'shipped_date'])) {
                $errorMessage = "The $attribute does not match the format d/m/Y.";
            } else if (in_array($key, ['warehouse_code', 'warehouse_location_code'])) {
                $errorMessage = "The $attribute must not be greater than 8 characters.";
            } else if ($key == 'defect_id') {
                $errorMessage = "The selected $attribute is invalid.";
            } else {
                $errorMessage = "The $attribute field is required.";
            }
            $this->assertEquals([
                'line' => 4,
                'attribute' => $attribute,
                'errors' => $errorMessage,
                'value' => $bwhInventoryLogAttributes[$key]
            ], $response['rows'][array_search($attribute, array_column($response['rows'], 'attribute'))]);
        }
        $this->assertDatabaseMissing('bwh_inventory_logs', Arr::only($bwhInventoryLogAttributes, $bwhInventoryLog->getFillable()));
    }

    private function createCorrectData($bwhInventoryLogAttributes, $warehouse = true, $warehouseLocation = true, $warehouseAndPlant = true, $inTransitInventoryLog = true)
    {
        if ($warehouse) {
            Warehouse::factory()->sequence(fn($sequence) => [
                'code' => $bwhInventoryLogAttributes['warehouse_code'],
                'plant_code' => $bwhInventoryLogAttributes['plant_code'],
                'warehouse_type' => Warehouse::TYPE_BWH
            ])->create();
        }

        if ($warehouseLocation) {
            WarehouseLocation::factory()->sequence(fn($sequence) => [
                'code' => $bwhInventoryLogAttributes['warehouse_location_code'],
                'plant_code' => $bwhInventoryLogAttributes['plant_code']
            ])->create();
        }

        if ($warehouseAndPlant) {
            WarehouseLocation::factory()->sequence(fn($sequence) => [
                'code' => $bwhInventoryLogAttributes['warehouse_location_code'],
                'warehouse_code' => $bwhInventoryLogAttributes['warehouse_code'],
                'plant_code' => $bwhInventoryLogAttributes['plant_code']
            ])->create();
        }

        if ($inTransitInventoryLog) {
            $inTransitInventoryLogCreate = InTransitInventoryLog::factory()
                ->sequence(fn($sequence) => Arr::only($bwhInventoryLogAttributes, $this->uniqueKeys))
                ->create();
        }

        return $inTransitInventoryLog ? $inTransitInventoryLogCreate : null;
    }

    private function createFileImport(array $bwhInventoryLogAttributes = [], array $headingsClass = [], array $dataContent = [], $failValidate = false)
    {
        if (empty($dataContent)) {
            foreach ($this->dataContent as $key) {
                $data[$key] = $bwhInventoryLogAttributes[$key];
            }
            $dataContent = [$data];
        }

        if (!$failValidate) {
            foreach ($dataContent as $index => $row) {
                $row['container_received'] = isset($row['container_received']) ? Carbon::parse($row['container_received'])->format('d/m/Y') : null;
                $row['devanned_date'] = isset($row['devanned_date']) ? Carbon::parse($row['devanned_date'])->format('d/m/Y') : null;
                $row['stored_date'] = isset($row['stored_date']) ? Carbon::parse($row['stored_date'])->format('d/m/Y') : null;
                $row['shipped_date'] = isset($row['shipped_date']) ? Carbon::parse($row['shipped_date'])->format('d/m/Y') : null;
                $dataContent[$index] = $row;
            }
        }

        request()->merge(['type' => $this->typeImportClass]);
        Excel::store(new TemplateExport(empty($headingsClass) ? $this->headingsClass : $headingsClass, $this->exportTitle, $dataContent), "testing/$this->typeImportClass.xlsx", 'public');

        return new UploadedFile(storage_path("app/public/testing/$this->typeImportClass.xlsx"), "$this->typeImportClass.xlsx", null, null, true);;
    }

    private function checkAssertFailValidate($response, $attribute, $errors, $value, $line = 4, $linkDownloadError = true)
    {
        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString($linkDownloadError ? env('AWS_URL') : '', $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        $this->assertEquals([
            'line' => $line,
            'attribute' => $attribute,
            'errors' => $errors,
            'value' => $value
        ], Arr::first($response['rows']));
    }

}
