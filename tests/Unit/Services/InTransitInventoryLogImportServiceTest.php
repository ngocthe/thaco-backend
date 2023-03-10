<?php

namespace Tests\Unit\Services;

use App\Exports\InTransitInventoryLogExport;
use App\Exports\TemplateExport;
use App\Imports\BoxTypeImport;
use App\Imports\InTransitInventoryLogImport;
use App\Models\Admin;
use App\Models\BoxType;
use App\Models\InTransitInventoryLog;
use App\Models\PartColor;
use App\Models\Plant;
use App\Models\Supplier;
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

class InTransitInventoryLogImportServiceTest extends TestCase
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
        $this->typeImportClass = 'in_transit_inventory_log';
        $this->headingsClass = InTransitInventoryLogImport::HEADING_ROW;
        $this->exportTitle = InTransitInventoryLogExport::TITLE;
        $this->dataContent = [
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
        ];
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function tearDown(): void
    {
        Storage::disk('public')->deleteDirectory('testing');
        parent::tearDown();
    }

    public function test_in_transit_inventory_log_import_insert()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes);
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_update()
    {
        $inTransitInventoryLogOrigin = InTransitInventoryLog::factory()->create();
        $inTransitInventoryLogOriginAttributes = $inTransitInventoryLogOrigin->getAttributes();
        $uniqueKeys = [
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'part_code',
            'part_color_code',
            'box_type_code',
            'plant_code'
        ];
        $inTransitInventoryLog = InTransitInventoryLog::factory()
            ->sequence(fn($sequence) => Arr::only($inTransitInventoryLogOriginAttributes, $uniqueKeys))
            ->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes);
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogOriginAttributes, $inTransitInventoryLogOrigin->getFillable()));
    }

    public function test_in_transit_inventory_log_import_missing_data()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes);
        $fileNew = $this->createFileImport([], [], [
            [
                'contract_code' => null,
                'invoice_code' => null,
                'bill_of_lading_code' => null,
                'container_code' => null,
                'case_code' => null,
                'part_code' => null,
                'part_color_code' => null,
                'box_type_code' => null,
                'box_quantity' => null,
                'part_quantity' => null,
                'unit' => null,
                'supplier_code' => null,
                'etd' => null,
                'container_shipped' => null,
                'eta' => null,
                'plant_code' => null
            ]
        ]);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, '', 'The import file has missing data.', '', 4, false);
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_handle_duplicate_error()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes);
        $dataRow = Arr::only($inTransitInventoryLogAttributes,$this->dataContent);
        $dataContent = [$dataRow, $dataRow];

        $fileNew = $this->createFileImport([], [], $dataContent);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $uniqueKeys = [
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'part_code',
            'part_color_code',
            'box_type_code',
            'plant_code'
        ];
        $uniqueData = [];
        foreach ($uniqueKeys as $key) {
            $uniqueData[$key] = $dataRow[$key];
        }
        $this->checkAssertFailValidate($response,
            'Contract No., Invoice No., B/L No., Container No., Case No., Part No., Part Color Code, Box Type Code, Plant Code',
            'Duplicate data', implode(',', $uniqueData), 5);
        $this->assertDatabaseHas('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_heading_row_in_correct()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes);
        //Make wrong heading class
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes, BoxTypeImport::HEADING_ROW);

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
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_with_fail_check_part_and_part_color()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes, false);
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No., Part Color Code, Plant Code', 'Part No, Part Color Code, Plant Code are not linked together.',
            implode(', ', [$inTransitInventoryLogAttributes['part_code'], $inTransitInventoryLogAttributes['part_color_code'], $inTransitInventoryLogAttributes['plant_code']]));
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_with_fail_check_part_and_box_type()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes, true, false);
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No, Box Type Code, Plant Code', 'Part No, Box Type Code, Plant Code are not linked together.',
            implode(', ', [$inTransitInventoryLogAttributes['part_code'], $inTransitInventoryLogAttributes['box_type_code'], $inTransitInventoryLogAttributes['plant_code']]));
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_with_fail_check_supplier()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes, true, true, false);
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Procurement Supplier Code', 'The Procurement Supplier Code does not exist.', $inTransitInventoryLogAttributes['supplier_code']);
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_with_fail_check_plant_code()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes, true, true, true, false);
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Plant Code', 'The Plant code does not exist.', $inTransitInventoryLogAttributes['plant_code']);
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_with_fail_handle_after_date_error()
    {
        $now = CarbonImmutable::now();
        //Make ETA before ETD
        $inTransitInventoryLog = InTransitInventoryLog::factory()->sequence(fn($sequence) => [
            'etd' => $now->addDays(1)->format('Y-m-d'),
            'eta' => $now->format('Y-m-d'),
        ])->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $this->createCorrectData($inTransitInventoryLogAttributes);
        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'ETA', 'ETA must come after or equal ETD', Carbon::parse($inTransitInventoryLogAttributes['eta'])->format('d/m/Y'));
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    public function test_in_transit_inventory_log_import_with_fail_rules_validate()
    {
        $inTransitInventoryLog = InTransitInventoryLog::factory()->sequence(fn($sequence) => [
            'contract_code' => null,
            'invoice_code' => null,
            'bill_of_lading_code' => null,
            'container_code' => null,
            'case_code' => null,
            'part_code' => null,
            'part_color_code' => null,
            'box_type_code' => null,
            'box_quantity' => null,
            'part_quantity' => null,
            'unit' => null,
            'supplier_code' => null,
            'etd' => null,
            'container_shipped' => null,
            'eta' => null,
            'plant_code' => strtoupper(Str::random(10))
        ])->make();
        $inTransitInventoryLogAttributes = $inTransitInventoryLog->getAttributes();

        $fileNew = $this->createFileImport($inTransitInventoryLogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString(env('AWS_URL'), $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        $index = 0;
        foreach (InTransitInventoryLogImport::MAP_HEADING_ROW as $key => $attribute) {
            if ($key == 'plant_code') {
                $errorMessage = "The $attribute must not be greater than 5 characters.";
            } else {
                $errorMessage = "The $attribute field is required.";
            }
            $this->assertEquals([
                'line' => 4,
                'attribute' => $attribute,
                'errors' => $errorMessage,
                'value' => $key == 'plant_code' ? $inTransitInventoryLogAttributes['plant_code'] : ''
            ], $response['rows'][array_search($attribute, array_column($response['rows'], 'attribute'))]);
            $index++;
        }
        $this->assertDatabaseMissing('in_transit_inventory_logs', Arr::only($inTransitInventoryLogAttributes, $inTransitInventoryLog->getFillable()));
    }

    private function createCorrectData($inTransitInventoryLogAttributes, $partColor = true, $boxType = true, $supplier = true, $plant = true)
    {
        if ($partColor) {
            PartColor::factory()->sequence(fn($sequence) => [
                'code' => $inTransitInventoryLogAttributes['part_color_code'],
                'part_code' => $inTransitInventoryLogAttributes['part_code'],
                'plant_code' => $inTransitInventoryLogAttributes['plant_code']
            ])->create();
        }

        if ($boxType) {
            BoxType::factory()->sequence(fn($sequence) => [
                'part_code' => $inTransitInventoryLogAttributes['part_code'],
                'code' => $inTransitInventoryLogAttributes['box_type_code'],
                'plant_code' => $inTransitInventoryLogAttributes['plant_code']
            ])->create();
        }

        if ($supplier) {
            Supplier::factory()->sequence(fn($sequence) => [
                'code' => $inTransitInventoryLogAttributes['supplier_code'],
            ])->create();
        }

        if ($plant) {
            Plant::factory()->sequence(fn($sequence) => [
                'code' => $inTransitInventoryLogAttributes['plant_code'],
            ])->create();
        }
    }

    private function createFileImport(array $inTransitInventoryLogAttributes = [], array $headingsClass = [], array $dataContent = [])
    {
        if (empty($dataContent)) {
            foreach ($this->dataContent as $key) {
                $data[$key] = $inTransitInventoryLogAttributes[$key];
            }
            $dataContent = [$data];
        }

        foreach ($dataContent as $index => $row) {
            $row['etd'] = isset($row['etd']) ? Carbon::parse($row['etd'])->format('d/m/Y') : null;
            $row['container_shipped'] = isset($row['etd']) ? Carbon::parse($row['container_shipped'])->format('d/m/Y') : null;
            $row['eta'] = isset($row['etd']) ? Carbon::parse($row['eta'])->format('d/m/Y') : null;
            $dataContent[$index] = $row;
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
