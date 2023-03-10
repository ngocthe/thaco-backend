<?php

namespace Tests\Unit\Services;

use App\Exports\LogicalInventoryPartAdjustmentExport;
use App\Exports\TemplateExport;
use App\Imports\BoxTypeImport;
use App\Imports\LogicalInventoryPartAdjustmentImport;
use App\Models\Admin;
use App\Models\LogicalInventory;
use App\Models\LogicalInventoryPartAdjustment;
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

class LogicalInventoryPartAdjustmentImportServiceTest extends TestCase
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
        $this->typeImportClass = 'warehouse_logical_adjustment_part';
        $this->headingsClass = LogicalInventoryPartAdjustmentImport::HEADING_ROW;
        $this->exportTitle = LogicalInventoryPartAdjustmentExport::TITLE;
        $this->dataContent = [
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

    public function test_logical_inventory_part_adjustment_import_insert()
    {
        $productionDate = now()->format('Y-m-d');
        $adjustmentQuantity = mt_rand(1, 1000);
        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->sequence(fn($sequence) => [
            'adjustment_date' => $productionDate,
            'old_quantity' => 0,
            'adjustment_quantity' => $adjustmentQuantity,
            'new_quantity' => $adjustmentQuantity
        ])->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryPartAdjustmentAttributes);

        $logicalInventory = LogicalInventory::factory()->sequence(fn($sequence) => [
            'plant_code' => $logicalInventoryPartAdjustmentAttributes['plant_code'],
            'part_code' => $logicalInventoryPartAdjustmentAttributes['part_code'],
            'part_color_code' => $logicalInventoryPartAdjustmentAttributes['part_color_code'],
            'quantity' => $logicalInventoryPartAdjustmentAttributes['adjustment_quantity'],
            'production_date' => $productionDate,
        ])->make();

        $fileNew = $this->createFileImport($logicalInventoryPartAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
        $this->assertDatabaseHas('logical_inventories', Arr::only($logicalInventory->getAttributes(), $logicalInventory->getFillable()));
    }

    public function test_logical_inventory_part_adjustment_import_insert_has_logical_inventory()
    {
        $productionDate = now()->format('Y-m-d');
        $adjustmentQuantity = mt_rand(1, 1000);

        $logicalInventory = LogicalInventory::factory()->sequence(fn($sequence) => [
            'production_date' => $productionDate,
        ])->create();
        $logicalInventoryAttributes = $logicalInventory->getAttributes();

        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->sequence(fn($sequence) => [
            'part_code' => $logicalInventoryAttributes['part_code'],
            'part_color_code' => $logicalInventoryAttributes['part_color_code'],
            'plant_code' => $logicalInventoryAttributes['plant_code'],
            'adjustment_date' => $productionDate,
            'old_quantity' => $logicalInventoryAttributes['quantity'],
            'new_quantity' => $logicalInventoryAttributes['quantity'] + $adjustmentQuantity,
            'adjustment_quantity' => $adjustmentQuantity
        ])->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryPartAdjustmentAttributes);

        $fileNew = $this->createFileImport($logicalInventoryPartAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $logicalInventoryNewAttributes = $logicalInventoryAttributes;
        $logicalInventoryNewAttributes = array_merge($logicalInventoryNewAttributes, [
            'old_quantity' => $logicalInventoryAttributes['quantity'],
            'new_quantity' => $logicalInventoryAttributes['quantity'] + $adjustmentQuantity,
            'quantity' => $logicalInventoryAttributes['quantity'] + $adjustmentQuantity,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id()
        ]);

        $this->assertNull($response);
        $this->assertDatabaseHas('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
        $this->assertDatabaseMissing('logical_inventories', Arr::only($logicalInventoryAttributes, $logicalInventory->getFillable()));
        $this->assertDatabaseHas('logical_inventories', Arr::only($logicalInventoryNewAttributes, $logicalInventory->getFillable()));
    }

    public function test_logical_inventory_part_adjustment_import_missing_data()
    {
        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryPartAdjustmentAttributes);
        $fileNew = $this->createFileImport([], [], [
            [
                'part_code' => null,
                'part_color_code' => null,
                'adjustment_quantity' => null,
                'plant_code' => null
            ]
        ]);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, '', 'The import file has missing data.', '', 4, false);
        $this->assertDatabaseMissing('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
    }

    public function test_logical_inventory_part_adjustment_import_handle_duplicate_error()
    {
        $adjustmentQuantity = mt_rand(1, 1000);
        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->sequence(fn($sequence) => [
            'old_quantity' => 0,
            'adjustment_quantity' => $adjustmentQuantity,
            'new_quantity' => $adjustmentQuantity
        ])->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryPartAdjustmentAttributes);
        foreach ($this->dataContent as $key) {
            $dataRow[$key] = $logicalInventoryPartAdjustmentAttributes[$key];
        }
        $dataContent = [$dataRow, $dataRow];

        $fileNew = $this->createFileImport([], [], $dataContent);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $uniqueKeys = [
            'part_code',
            'part_color_code',
            'plant_code'
        ];
        $uniqueData = [];
        foreach ($uniqueKeys as $key) {
            $uniqueData[$key] = $dataRow[$key];
        }
        $this->checkAssertFailValidate($response,
            'Part No., Part Color Code, Plant Code',
            'Duplicate data', implode(',', $uniqueData), 5);
        $this->assertDatabaseHas('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
    }

    public function test_logical_inventory_part_adjustment_import_heading_row_in_correct()
    {
        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryPartAdjustmentAttributes);
        //Make wrong heading class
        $fileNew = $this->createFileImport($logicalInventoryPartAdjustmentAttributes, BoxTypeImport::HEADING_ROW);

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
        $this->assertDatabaseMissing('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
    }

    public function test_logical_inventory_part_adjustment_import_with_fail_check_part_and_part_color()
    {
        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryPartAdjustmentAttributes, false);
        $fileNew = $this->createFileImport($logicalInventoryPartAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No., Part Color Code, Plant Code', 'Part No, Part Color Code, Plant Code are not linked together.',
            implode(', ', [$logicalInventoryPartAdjustmentAttributes['part_code'], $logicalInventoryPartAdjustmentAttributes['part_color_code'], $logicalInventoryPartAdjustmentAttributes['plant_code']]));
        $this->assertDatabaseMissing('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
    }

    public function test_logical_inventory_part_adjustment_import_with_fail_check_plant_code()
    {
        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryPartAdjustmentAttributes, true, false);
        $fileNew = $this->createFileImport($logicalInventoryPartAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Plant Code', 'The Plant code does not exist.', $logicalInventoryPartAdjustmentAttributes['plant_code']);
        $this->assertDatabaseMissing('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
    }

    public function test_logical_inventory_part_adjustment_import_with_fail_rules_validate()
    {
        $logicalInventoryPartAdjustment = LogicalInventoryPartAdjustment::factory()->sequence(fn($sequence) => [
            'part_code' => null,
            'part_color_code' => null,
            'adjustment_quantity' => null,
            'plant_code' => strtoupper(Str::random(10))
        ])->make();
        $logicalInventoryPartAdjustmentAttributes = $logicalInventoryPartAdjustment->getAttributes();

        $fileNew = $this->createFileImport($logicalInventoryPartAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString(env('AWS_URL'), $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        $index = 0;
        foreach (LogicalInventoryPartAdjustmentImport::MAP_HEADING_ROW as $key => $attribute) {
            if ($key == 'plant_code') {
                $errorMessage = "The $attribute must not be greater than 5 characters.";
            } else {
                $errorMessage = "The $attribute field is required.";
            }
            $this->assertEquals([
                'line' => 4,
                'attribute' => $attribute,
                'errors' => $errorMessage,
                'value' => $key == 'plant_code' ? $logicalInventoryPartAdjustmentAttributes['plant_code'] : ''
            ], $response['rows'][array_search($attribute, array_column($response['rows'], 'attribute'))]);
            $index++;
        }
        $this->assertDatabaseMissing('logical_inventory_part_adjustments', Arr::only($logicalInventoryPartAdjustmentAttributes, $logicalInventoryPartAdjustment->getFillable()));
    }

    private function createCorrectData($logicalInventoryPartAdjustmentAttributes, $partColor = true, $plant = true)
    {
        if ($partColor) {
            PartColor::factory()->sequence(fn($sequence) => [
                'code' => $logicalInventoryPartAdjustmentAttributes['part_color_code'],
                'part_code' => $logicalInventoryPartAdjustmentAttributes['part_code'],
                'plant_code' => $logicalInventoryPartAdjustmentAttributes['plant_code']
            ])->create();
        }

        if ($plant) {
            Plant::factory()->sequence(fn($sequence) => [
                'code' => $logicalInventoryPartAdjustmentAttributes['plant_code'],
            ])->create();
        }
    }

    private function createFileImport(array $logicalInventoryPartAdjustmentAttributes = [], array $headingsClass = [], array $dataContent = [])
    {
        if (empty($dataContent)) {
            foreach ($this->dataContent as $key) {
                $data[$key] = $logicalInventoryPartAdjustmentAttributes[$key];
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
