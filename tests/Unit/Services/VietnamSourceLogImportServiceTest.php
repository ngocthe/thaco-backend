<?php

namespace Tests\Unit\Services;

use App\Exports\TemplateExport;
use App\Exports\VietnamSourceRequestExport;
use App\Imports\BoxTypeImport;
use App\Imports\VietnamSourceLogLogImport;
use App\Models\Admin;
use App\Models\BoxType;
use App\Models\PartColor;
use App\Models\Plant;
use App\Models\Supplier;
use App\Models\VietnamSourceLog;
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

class VietnamSourceLogImportServiceTest extends TestCase
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
        $this->typeImportClass = 'vietnam_source_log';
        $this->headingsClass = VietnamSourceLogLogImport::HEADING_ROW;
        $this->exportTitle = VietnamSourceRequestExport::TITLE;
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
            'delivery_date',
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

    public function test_vietnam_source_logs_import_insert()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes);
        $fileNew = $this->createFileImport($vietnamSourcelogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_update()
    {
        $vietnamSourcelogOrigin = VietnamSourcelog::factory()->create();
        $vietnamSourcelogOriginAttributes = $vietnamSourcelogOrigin->getAttributes();
        $uniqueKeys = [
            'contract_code',
            'part_code',
            'part_color_code',
            'box_type_code',
            'plant_code'
        ];
        $vietnamSourcelog = VietnamSourcelog::factory()
            ->sequence(fn($sequence) => Arr::only($vietnamSourcelogOriginAttributes, $uniqueKeys))
            ->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes);
        $fileNew = $this->createFileImport($vietnamSourcelogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogOriginAttributes, $vietnamSourcelogOrigin->getFillable()));
    }

    public function test_vietnam_source_logs_import_missing_data()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes);
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
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_handle_duplicate_error()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes);
        $dataRow = Arr::only($vietnamSourcelogAttributes,$this->dataContent);
        $dataContent = [$dataRow, $dataRow];

        $fileNew = $this->createFileImport([], [], $dataContent);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $uniqueKeys = [
            'contract_code',
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
            'Contract No., Part No., Part Color Code, Box Type Code, Plant Code',
            'Duplicate data', implode(',', $uniqueData), 5);
        $this->assertDatabaseHas('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_heading_row_in_correct()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes);
        //Make wrong heading class
        $fileNew = $this->createFileImport($vietnamSourcelogAttributes, BoxTypeImport::HEADING_ROW);

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
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_with_fail_check_part_and_part_color()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes, false);
        $fileNew = $this->createFileImport($vietnamSourcelogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No., Part Color Code, Plant Code', 'Part No, Part Color Code, Plant Code are not linked together.',
            implode(', ', [$vietnamSourcelogAttributes['part_code'], $vietnamSourcelogAttributes['part_color_code'], $vietnamSourcelogAttributes['plant_code']]));
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_with_fail_check_part_and_box_type()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes, true, false);
        $fileNew = $this->createFileImport($vietnamSourcelogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No, Box Type Code, Plant Code', 'Part No, Box Type Code, Plant Code are not linked together.',
            implode(', ', [$vietnamSourcelogAttributes['part_code'], $vietnamSourcelogAttributes['box_type_code'], $vietnamSourcelogAttributes['plant_code']]));
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_with_fail_check_supplier()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes, true, true, false);
        $fileNew = $this->createFileImport($vietnamSourcelogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Procurement Supplier Code', 'The Procurement Supplier Code does not exist.', $vietnamSourcelogAttributes['supplier_code']);
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_with_fail_check_plant_code()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $this->createCorrectData($vietnamSourcelogAttributes, true, true, true, false);
        $fileNew = $this->createFileImport($vietnamSourcelogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Plant Code', 'The Plant code does not exist.', $vietnamSourcelogAttributes['plant_code']);
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    public function test_vietnam_source_logs_import_with_fail_rules_validate()
    {
        $vietnamSourcelog = VietnamSourcelog::factory()->sequence(fn($sequence) => [
            'contract_code' => null,
            'invoice_code' => strtoupper(Str::random(11)),
            'bill_of_lading_code' => strtoupper(Str::random(14)),
            'container_code' => null,
            'case_code' => strtoupper(Str::random(10)),
            'part_code' => null,
            'part_color_code' => null,
            'box_type_code' => null,
            'box_quantity' => null,
            'part_quantity' => null,
            'unit' => null,
            'supplier_code' => null,
            'delivery_date' => null,
            'plant_code' => null
        ])->make();
        $vietnamSourcelogAttributes = $vietnamSourcelog->getAttributes();

        $fileNew = $this->createFileImport($vietnamSourcelogAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString(env('AWS_URL'), $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        $index = 0;
        foreach (VietnamSourceLogLogImport::MAP_HEADING_ROW as $key => $attribute) {
            if ($key == 'invoice_code') {
                $errorMessage = "The $attribute must not be greater than 10 characters.";
            } else if ($key == 'bill_of_lading_code') {
                $errorMessage = "The $attribute must not be greater than 13 characters.";
            } else if ($key == 'case_code') {
                $errorMessage = "The $attribute must not be greater than 9 characters.";
            } else {
                $errorMessage = "The $attribute field is required.";
            }
            $this->assertEquals([
                'line' => 4,
                'attribute' => $attribute,
                'errors' => $errorMessage,
                'value' => in_array($key, ['invoice_code', 'bill_of_lading_code', 'case_code']) ? $vietnamSourcelogAttributes[$key] : ''
            ], $response['rows'][array_search($attribute, array_column($response['rows'], 'attribute'))]);
            $index++;
        }
        $this->assertDatabaseMissing('vietnam_source_logs', Arr::only($vietnamSourcelogAttributes, $vietnamSourcelog->getFillable()));
    }

    private function createCorrectData($vietnamSourcelogAttributes, $partColor = true, $boxType = true, $supplier = true, $plant = true)
    {
        if ($partColor) {
            PartColor::factory()->sequence(fn($sequence) => [
                'code' => $vietnamSourcelogAttributes['part_color_code'],
                'part_code' => $vietnamSourcelogAttributes['part_code'],
                'plant_code' => $vietnamSourcelogAttributes['plant_code']
            ])->create();
        }

        if ($boxType) {
            BoxType::factory()->sequence(fn($sequence) => [
                'part_code' => $vietnamSourcelogAttributes['part_code'],
                'code' => $vietnamSourcelogAttributes['box_type_code'],
                'plant_code' => $vietnamSourcelogAttributes['plant_code']
            ])->create();
        }

        if ($supplier) {
            Supplier::factory()->sequence(fn($sequence) => [
                'code' => $vietnamSourcelogAttributes['supplier_code'],
            ])->create();
        }

        if ($plant) {
            Plant::factory()->sequence(fn($sequence) => [
                'code' => $vietnamSourcelogAttributes['plant_code'],
            ])->create();
        }
    }

    private function createFileImport(array $vietnamSourcelogAttributes = [], array $headingsClass = [], array $dataContent = [])
    {
        if (empty($dataContent)) {
            foreach ($this->dataContent as $key) {
                $data[$key] = $vietnamSourcelogAttributes[$key];
            }
            $dataContent = [$data];
        }

        foreach ($dataContent as $index => $row) {
            $row['delivery_date'] = isset($row['delivery_date']) ? Carbon::parse($row['delivery_date'])->format('d/m/Y') : null;
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
