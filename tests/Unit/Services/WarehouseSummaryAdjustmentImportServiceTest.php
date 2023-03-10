<?php

namespace Tests\Unit\Services;

use App\Exports\TemplateExport;
use App\Exports\WarehouseSummaryAdjustmentExport;
use App\Imports\BoxTypeImport;
use App\Imports\WarehouseSummaryAdjustmentImport;
use App\Models\Admin;
use App\Models\Procurement;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Models\WarehouseSummaryAdjustment;
use App\Models\PartColor;
use App\Models\Plant;
use App\Services\DataInventoryImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class WarehouseSummaryAdjustmentImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $typeImportClass;
    private $dataContent;
    private $headingsClass;
    private $exportTitle;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new DataInventoryImportService();
        $this->typeImportClass = 'warehouse_summary_adjustment';
        $this->headingsClass = WarehouseSummaryAdjustmentImport::HEADING_ROW;
        $this->exportTitle = WarehouseSummaryAdjustmentExport::TITLE;
        $this->dataContent = [
            'warehouse_code',
            'part_code',
            'part_color_code',
            'adjustment_quantity',
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

    public function test_warehouse_summary_adjustment_import_insert()
    {
        $adjustmentQuantity = mt_rand(1, 1000);
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->sequence(fn($sequence) => [
            'old_quantity' => 0,
            'adjustment_quantity' => $adjustmentQuantity,
            'new_quantity' => $adjustmentQuantity
        ])->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $warehoue = $this->createCorrectData($warehouseSummaryAdjustmentAttributes);

        $warehouseInventorySummary = WarehouseInventorySummary::factory()->sequence(fn($sequence) =>
        array_merge($warehouseSummaryAdjustmentAttributes, [
            'quantity' => $warehouseSummaryAdjustmentAttributes['adjustment_quantity'],
            'unit' => null,
            'warehouse_type' => $warehoue->getAttribute('warehouse_type'),
        ])
        )->make();

        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_insert_has_procurement()
    {
        $adjustmentQuantity = mt_rand(1, 1000);
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->sequence(fn($sequence) => [
            'old_quantity' => 0,
            'adjustment_quantity' => $adjustmentQuantity,
            'new_quantity' => $adjustmentQuantity
        ])->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $warehoue = $this->createCorrectData($warehouseSummaryAdjustmentAttributes);
        $procurementFactory = Procurement::factory()->sequence(fn($sequence) => [
            'plant_code' => $warehouseSummaryAdjustmentAttributes['plant_code'],
            'part_code' => $warehouseSummaryAdjustmentAttributes['part_code'],
            'part_color_code' => $warehouseSummaryAdjustmentAttributes['part_color_code']
        ])->create();
        $procurementFactoryAttributes = $procurementFactory->getAttributes();

        $warehouseInventorySummary = WarehouseInventorySummary::factory()->sequence(fn($sequence) =>
            array_merge($warehouseSummaryAdjustmentAttributes, [
                'quantity' => $warehouseSummaryAdjustmentAttributes['adjustment_quantity'],
                'unit' => $procurementFactoryAttributes['unit'],
                'warehouse_type' => $warehoue->getAttribute('warehouse_type'),
            ])
        )->make();

        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_insert_has_warehouse_inventory_summary()
    {
        $warehouseInventorySummary = WarehouseInventorySummary::factory()->create();
        $warehouseInventorySummaryAttributes = $warehouseInventorySummary->getAttributes();

        $adjustmentQuantity = mt_rand(1, 1000);
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->sequence(fn($sequence) => [
            'warehouse_code' => $warehouseInventorySummaryAttributes['warehouse_code'],
            'part_code' => $warehouseInventorySummaryAttributes['part_code'],
            'part_color_code' => $warehouseInventorySummaryAttributes['part_color_code'],
            'plant_code' => $warehouseInventorySummaryAttributes['plant_code'],
            'adjustment_quantity' => $adjustmentQuantity,
            'old_quantity' => $warehouseInventorySummaryAttributes['quantity'],
            'new_quantity' => $warehouseInventorySummaryAttributes['quantity'] + $adjustmentQuantity
        ])->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $this->createCorrectData($warehouseSummaryAdjustmentAttributes);;
        $warehouseInventorySummaryNewAttributes = $warehouseInventorySummaryAttributes;
        Arr::set($warehouseInventorySummaryNewAttributes, 'quantity', $warehouseInventorySummaryAttributes['quantity'] + $adjustmentQuantity);

        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
        $this->assertDatabaseMissing('warehouse_inventory_summaries', Arr::only($warehouseInventorySummary->getAttributes(), $warehouseInventorySummary->getFillable()));
        $this->assertDatabaseHas('warehouse_inventory_summaries', Arr::only($warehouseInventorySummaryNewAttributes, $warehouseInventorySummary->getFillable()));
    }


    public function test_warehouse_summary_adjustment_import_missing_data()
    {
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $this->createCorrectData($warehouseSummaryAdjustmentAttributes);
        $fileNew = $this->createFileImport([], [], [
            [
                'warehouse_code' => null,
                'part_code' => null,
                'part_color_code' => null,
                'adjustment_quantity' => null,
                'plant_code' => null
            ]
        ]);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, '', 'The import file has missing data.', '', 4, false);
        $this->assertDatabaseMissing('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_handle_duplicate_error()
    {
        $adjustmentQuantity = mt_rand(1, 1000);
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->sequence(fn($sequence) => [
            'old_quantity' => 0,
            'adjustment_quantity' => $adjustmentQuantity,
            'new_quantity' => $adjustmentQuantity
        ])->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $this->createCorrectData($warehouseSummaryAdjustmentAttributes);
        foreach ($this->dataContent as $key) {
            $dataRow[$key] = $warehouseSummaryAdjustmentAttributes[$key];
        }
        $dataContent = [$dataRow, $dataRow];

        $fileNew = $this->createFileImport([], [], $dataContent);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $uniqueKeys = [
            'warehouse_code',
            'part_code',
            'part_color_code',
            'plant_code'
        ];
        $uniqueData = [];
        foreach ($uniqueKeys as $key) {
            $uniqueData[$key] = $dataRow[$key];
        }
        $this->checkAssertFailValidate($response,
            'Warehouse Code, Part No., Part Color Code, Plant Code',
            'Duplicate data', implode(',', $uniqueData), 5);
        $this->assertDatabaseHas('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_heading_row_in_correct()
    {
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $this->createCorrectData($warehouseSummaryAdjustmentAttributes);
        //Make wrong heading class
        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes, BoxTypeImport::HEADING_ROW);

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
        $this->assertDatabaseMissing('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_with_fail_check_part_and_part_color()
    {
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $this->createCorrectData($warehouseSummaryAdjustmentAttributes, false);
        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No., Part Color Code, Plant Code', 'Part No, Part Color Code, Plant Code are not linked together.',
            implode(', ', [$warehouseSummaryAdjustmentAttributes['part_code'], $warehouseSummaryAdjustmentAttributes['part_color_code'], $warehouseSummaryAdjustmentAttributes['plant_code']]));
        $this->assertDatabaseMissing('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_with_fail_check_warehouse()
    {
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $this->createCorrectData($warehouseSummaryAdjustmentAttributes, true, false);
        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Warehouse Code, Plant Code', 'Warehouse Code, Plant Code are not linked together.',
            implode(', ', [$warehouseSummaryAdjustmentAttributes['warehouse_code'], $warehouseSummaryAdjustmentAttributes['plant_code']]));
        $this->assertDatabaseMissing('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_with_fail_check_plant_code()
    {
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $this->createCorrectData($warehouseSummaryAdjustmentAttributes, true, true, false);
        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Plant Code', 'The Plant code does not exist.', $warehouseSummaryAdjustmentAttributes['plant_code']);
        $this->assertDatabaseMissing('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
    }

    public function test_warehouse_summary_adjustment_import_with_fail_rules_validate()
    {
        $warehouseSummaryAdjustment = WarehouseSummaryAdjustment::factory()->sequence(fn($sequence) => [
            'warehouse_code' => null,
            'part_code' => null,
            'part_color_code' => null,
            'adjustment_quantity' => null,
            'plant_code' => strtoupper(Str::random(10))
        ])->make();
        $warehouseSummaryAdjustmentAttributes = $warehouseSummaryAdjustment->getAttributes();

        $fileNew = $this->createFileImport($warehouseSummaryAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString(env('AWS_URL'), $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        $index = 0;
        foreach (WarehouseSummaryAdjustmentImport::MAP_HEADING_ROW as $key => $attribute) {
            if ($key == 'plant_code') {
                $errorMessage = "The $attribute must not be greater than 5 characters.";
            } else {
                $errorMessage = "The $attribute field is required.";
            }
            $this->assertEquals([
                'line' => 4,
                'attribute' => $attribute,
                'errors' => $errorMessage,
                'value' => $key == 'plant_code' ? $warehouseSummaryAdjustmentAttributes['plant_code'] : ''
            ], $response['rows'][array_search($attribute, array_column($response['rows'], 'attribute'))]);
            $index++;
        }
        $this->assertDatabaseMissing('warehouse_summary_adjustments', Arr::only($warehouseSummaryAdjustmentAttributes, $warehouseSummaryAdjustment->getFillable()));
    }

    private function createCorrectData($warehouseSummaryAdjustmentAttributes, $partColor = true, $warehouse = true, $plant = true)
    {
        if ($partColor) {
            PartColor::factory()->sequence(fn($sequence) => [
                'code' => $warehouseSummaryAdjustmentAttributes['part_color_code'],
                'part_code' => $warehouseSummaryAdjustmentAttributes['part_code'],
                'plant_code' => $warehouseSummaryAdjustmentAttributes['plant_code']
            ])->create();
        }

        if ($warehouse) {
            $warehoue = Warehouse::factory()->sequence(fn($sequence) => [
                'code' => $warehouseSummaryAdjustmentAttributes['warehouse_code'],
                'plant_code' => $warehouseSummaryAdjustmentAttributes['plant_code']
            ])->create();
        }

        if ($plant) {
            Plant::factory()->sequence(fn($sequence) => [
                'code' => $warehouseSummaryAdjustmentAttributes['plant_code'],
            ])->create();
        }

        return $warehoue ?? null;
    }

    private function createFileImport(array $warehouseSummaryAdjustmentAttributes = [], array $headingsClass = [], array $dataContent = [])
    {
        if (empty($dataContent)) {
            foreach ($this->dataContent as $key) {
                $data[$key] = $warehouseSummaryAdjustmentAttributes[$key];
            }
            $dataContent = [$data];
        }

        request()->merge(['type' => $this->typeImportClass]);
        Excel::store(new TemplateExport(empty($headingsClass) ? $this->headingsClass : $headingsClass, $this->exportTitle, $dataContent), "testing/$this->typeImportClass.xlsx", 'public');

        return new UploadedFile(storage_path("app/public/testing/$this->typeImportClass.xlsx"), "$this->typeImportClass.xlsx", null, null, true);
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
