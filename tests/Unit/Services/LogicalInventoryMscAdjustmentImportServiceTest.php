<?php

namespace Tests\Unit\Services;

use App\Exports\LogicalInventoryMscAdjustmentExport;
use App\Exports\TemplateExport;
use App\Imports\BoxTypeImport;
use App\Imports\LogicalInventoryMscAdjustmentImport;
use App\Models\Admin;
use App\Models\Bom;
use App\Models\LogicalInventory;
use App\Models\LogicalInventoryMscAdjustment;
use App\Models\Msc;
use App\Models\PartColor;
use App\Models\Plant;
use App\Models\VehicleColor;
use App\Services\DataInventoryImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LogicalInventoryMscAdjustmentImportServiceTest extends TestCase
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
        $this->typeImportClass = 'warehouse_logical_adjustment_msc';
        $this->headingsClass = LogicalInventoryMscAdjustmentImport::HEADING_ROW;
        $this->exportTitle = LogicalInventoryMscAdjustmentExport::TITLE;
        $this->dataContent = [
            'msc_code',
            'vehicle_color_code',
            'adjustment_quantity',
            'production_date',
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

    public function test_logical_inventory_msc_adjustment_import_insert()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes);

        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_insert_has_bom_and_not_has_logical_inventory()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $bom = Bom::factory()->sequence(fn($sequence) => [
            'msc_code' => $logicalInventoryMscAdjustmentAttributes['msc_code'],
            'plant_code' => $logicalInventoryMscAdjustmentAttributes['plant_code'],
        ])->create();
        $bomAttributes = $bom->getAttributes();

        $logicalInventory = LogicalInventory::factory()->sequence(fn($sequence) => [
            'plant_code' => $bomAttributes['plant_code'],
            'part_code' => $bomAttributes['part_code'],
            'part_color_code' => $bomAttributes['part_color_code'],
            'quantity' => $bomAttributes['quantity'] * $logicalInventoryMscAdjustmentAttributes['adjustment_quantity'],
            'production_date' => $logicalInventoryMscAdjustmentAttributes['production_date']
        ])->make();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes);

        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
        $this->assertDatabaseHas('logical_inventories', Arr::only($logicalInventory->getAttributes(), $logicalInventory->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_insert_has_bom_with_part_color_code_xx_and_not_has_logical_inventory()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $bom = Bom::factory()->sequence(fn($sequence) => [
            'msc_code' => $logicalInventoryMscAdjustmentAttributes['msc_code'],
            'plant_code' => $logicalInventoryMscAdjustmentAttributes['plant_code'],
            'part_color_code' => 'XX'
        ])->create();
        $bomAttributes = $bom->getAttributes();

        $partColor = PartColor::factory()->sequence(fn($sequence) => [
            'part_code' => $bomAttributes['part_code'],
            'vehicle_color_code' => $logicalInventoryMscAdjustmentAttributes['vehicle_color_code']
        ])->create();

        $logicalInventory = LogicalInventory::factory()->sequence(fn($sequence) => [
            'plant_code' => $bomAttributes['plant_code'],
            'part_code' => $bomAttributes['part_code'],
            'part_color_code' => $partColor->getAttribute('code'),
            'quantity' => $bomAttributes['quantity'] * $logicalInventoryMscAdjustmentAttributes['adjustment_quantity'],
            'production_date' => $logicalInventoryMscAdjustmentAttributes['production_date']
        ])->make();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes);

        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
        $this->assertDatabaseHas('logical_inventories', Arr::only($logicalInventory->getAttributes(), $logicalInventory->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_insert_has_bom_and_has_logical_inventory()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $bom = Bom::factory()->sequence(fn($sequence) => [
            'msc_code' => $logicalInventoryMscAdjustmentAttributes['msc_code'],
            'plant_code' => $logicalInventoryMscAdjustmentAttributes['plant_code'],
        ])->create();
        $bomAttributes = $bom->getAttributes();

        $logicalInventory = LogicalInventory::factory()->sequence(fn($sequence) => [
            'plant_code' => $bomAttributes['plant_code'],
            'part_code' => $bomAttributes['part_code'],
            'part_color_code' => $bomAttributes['part_color_code'],
            'production_date' => $logicalInventoryMscAdjustmentAttributes['production_date']
        ])->create();
        $logicalInventoryAttributes = $logicalInventory->getAttributes();
        $logicalInventoryNewAttributes = $logicalInventoryAttributes;
        $logicalInventoryNewAttributes = array_merge($logicalInventoryNewAttributes, [
            'quantity' => $logicalInventoryAttributes['quantity'] + ($bomAttributes['quantity'] * $logicalInventoryMscAdjustmentAttributes['adjustment_quantity']),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id()
        ]);

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes);

        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
        $this->assertDatabaseMissing('logical_inventories', Arr::only($logicalInventoryAttributes, $logicalInventory->getFillable()));
        $this->assertDatabaseHas('logical_inventories', Arr::only($logicalInventoryNewAttributes, $logicalInventory->getFillable()));    }


    public function test_logical_inventory_msc_adjustment_import_missing_data()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes);
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
        $this->assertDatabaseMissing('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_handle_duplicate_error()
    {
        $adjustmentQuantity = mt_rand(1, 1000);
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->sequence(fn($sequence) => [
            'old_quantity' => 0,
            'adjustment_quantity' => $adjustmentQuantity,
            'new_quantity' => $adjustmentQuantity
        ])->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes);
        foreach ($this->dataContent as $key) {
            $dataRow[$key] = $logicalInventoryMscAdjustmentAttributes[$key];
        }
        $dataContent = [$dataRow, $dataRow];

        $fileNew = $this->createFileImport([], [], $dataContent);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $uniqueKeys = [
            'msc_code',
            'vehicle_color_code',
            'production_date',
            'plant_code'
        ];
        $uniqueData = [];
        foreach ($uniqueKeys as $key) {
            if ($key == 'production_date') {
                $uniqueData[$key] = Carbon::parse($dataRow[$key])->format('d/m/Y');
            } else {
                $uniqueData[$key] = $dataRow[$key];
            }

        }
        $this->checkAssertFailValidate($response,
            'MSC Code, Exterior Color Code, Production Date, Plant Code',
            'Duplicate data', implode(',', $uniqueData), 5);
        $this->assertDatabaseHas('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_heading_row_in_correct()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes);
        //Make wrong heading class
        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes, BoxTypeImport::HEADING_ROW);

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
        $this->assertDatabaseMissing('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_with_fail_check_msc()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes, false);
        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'MSC, Plant Code', 'MSC, Plant Code are not linked together.',
            implode(', ', [$logicalInventoryMscAdjustmentAttributes['msc_code'], $logicalInventoryMscAdjustmentAttributes['plant_code']]));
        $this->assertDatabaseMissing('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_with_fail_check_vehicle_color()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes, true, false);
        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Exterior Color Code, Plant Code', 'Exterior Color Code, Plant Code are not linked together.',
            implode(', ', [$logicalInventoryMscAdjustmentAttributes['vehicle_color_code'], $logicalInventoryMscAdjustmentAttributes['plant_code']]));
        $this->assertDatabaseMissing('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_with_fail_check_plant_code()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $this->createCorrectData($logicalInventoryMscAdjustmentAttributes, true, true, false);
        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Plant Code', 'The Plant code does not exist.', $logicalInventoryMscAdjustmentAttributes['plant_code']);
        $this->assertDatabaseMissing('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    public function test_logical_inventory_msc_adjustment_import_with_fail_rules_validate()
    {
        $logicalInventoryMscAdjustment = LogicalInventoryMscAdjustment::factory()->sequence(fn($sequence) => [
            'msc_code' => null,
            'adjustment_quantity' => null,
            'vehicle_color_code' => null,
            'production_date' => null,
            'plant_code' => strtoupper(Str::random(10))
        ])->make();
        $logicalInventoryMscAdjustmentAttributes = $logicalInventoryMscAdjustment->getAttributes();

        $fileNew = $this->createFileImport($logicalInventoryMscAdjustmentAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString(env('AWS_URL'), $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        $index = 0;
        foreach (LogicalInventoryMscAdjustmentImport::MAP_HEADING_ROW as $key => $attribute) {
            if ($key == 'plant_code') {
                $errorMessage = "The $attribute must not be greater than 5 characters.";
            } else {
                $errorMessage = "The $attribute field is required.";
            }
            $this->assertEquals([
                'line' => 4,
                'attribute' => $attribute,
                'errors' => $errorMessage,
                'value' => $key == 'plant_code' ? $logicalInventoryMscAdjustmentAttributes['plant_code'] : ''
            ], $response['rows'][array_search($attribute, array_column($response['rows'], 'attribute'))]);
            $index++;
        }
        $this->assertDatabaseMissing('logical_inventory_msc_adjustments', Arr::only($logicalInventoryMscAdjustmentAttributes, $logicalInventoryMscAdjustment->getFillable()));
    }

    private function createCorrectData($logicalInventoryMscAdjustmentAttributes, $msc = true, $vehicleColor = true, $plant = true)
    {
        if (!$plant) {
            $plantCreate = Plant::factory()->sequence(fn($sequence) => [
                'code' => $logicalInventoryMscAdjustmentAttributes['plant_code'],
            ])->withDeleted(now())->create();
        } else {
            $plantCreate = Plant::factory()->sequence(fn($sequence) => [
                'code' => $logicalInventoryMscAdjustmentAttributes['plant_code'],
            ])->create();
        }

        if ($msc) {
            Msc::factory()->sequence(fn($sequence) => [
                'code' => $logicalInventoryMscAdjustmentAttributes['msc_code'],
                'plant_code' => $plantCreate->getAttribute('code')
            ])->create();
        }

        if ($vehicleColor) {
            VehicleColor::factory()->sequence(fn($sequence) => [
                'code' => $logicalInventoryMscAdjustmentAttributes['vehicle_color_code'],
                'plant_code' => $plantCreate->getAttribute('code')
            ])->create();
        }
    }

    private function createFileImport(array $logicalInventoryMscAdjustmentAttributes = [], array $headingsClass = [], array $dataContent = [])
    {
        if (empty($dataContent)) {
            foreach ($this->dataContent as $key) {
                $data[$key] = $logicalInventoryMscAdjustmentAttributes[$key];
            }
            $dataContent = [$data];
        }

        foreach ($dataContent as $index => $row) {
            $row['production_date'] = isset($row['production_date']) ? Carbon::parse($row['production_date'])->format('d/m/Y') : null;
            $dataContent[$index] = $row;
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
