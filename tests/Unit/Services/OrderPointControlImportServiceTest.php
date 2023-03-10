<?php

namespace Tests\Unit\Services;

use App\Exports\OrderPointControlExport;
use App\Exports\TemplateExport;
use App\Imports\BoxTypeImport;
use App\Imports\OrderPointControlImport;
use App\Models\Admin;
use App\Models\BoxType;
use App\Models\OrderPointControl;
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

class OrderPointControlImportServiceTest extends TestCase
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
        $this->typeImportClass = 'order_point_control';
        $this->headingsClass = OrderPointControlImport::HEADING_ROW;
        $this->exportTitle = OrderPointControlExport::TITLE;
        $this->dataContent = [
            'part_code',
            'part_color_code',
            'standard_stock',
            'ordering_lot',
            'box_type_code',
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

    public function test_order_point_control_import_insert()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes);
        $fileNew = $this->createFileImport($orderPointControlAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertNull($response);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    public function test_order_point_control_import_missing_data()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes);
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
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    public function test_order_point_control_import_handle_duplicate_error()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes);
        foreach ($this->dataContent as $key) {
            $dataRow[$key] = $orderPointControlAttributes[$key];
        }
        $dataContent = [$dataRow, $dataRow];

        $fileNew = $this->createFileImport([], [], $dataContent);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $uniqueKeys = [
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
            'Part No., Part Color Code, Box Type Code, Plant Code',
            'Duplicate data', implode(',', $uniqueData), 5);
        $this->assertDatabaseHas('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    public function test_order_point_control_import_heading_row_in_correct()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes);
        //Make wrong heading class
        $fileNew = $this->createFileImport($orderPointControlAttributes, BoxTypeImport::HEADING_ROW);

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
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    public function test_order_point_control_import_with_fail_check_part_and_part_color()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes, false);
        $fileNew = $this->createFileImport($orderPointControlAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No., Part Color Code, Plant Code', 'Part No, Part Color Code, Plant Code are not linked together.',
            implode(', ', [$orderPointControlAttributes['part_code'], $orderPointControlAttributes['part_color_code'], $orderPointControlAttributes['plant_code']]));
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    public function test_order_point_control_import_with_fail_check_part_and_box_type()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes, true, false);
        $fileNew = $this->createFileImport($orderPointControlAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No, Box Type Code, Plant Code', 'Part No, Box Type Code, Plant Code are not linked together.',
            implode(', ', [$orderPointControlAttributes['part_code'], $orderPointControlAttributes['box_type_code'], $orderPointControlAttributes['plant_code']]));
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    public function test_order_point_control_import_with_fail_check_plant_code()
    {
        $orderPointControl = OrderPointControl::factory()->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes, true, true, false);
        $fileNew = $this->createFileImport($orderPointControlAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Plant Code', 'The Plant code does not exist.', $orderPointControlAttributes['plant_code']);
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    public function test_order_point_control_import_with_fail_check_unique_data()
    {
        $orderPointControl = OrderPointControl::factory()->create();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $this->createCorrectData($orderPointControlAttributes);
        $fileNew = $this->createFileImport($orderPointControlAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->checkAssertFailValidate($response, 'Part No., Part Color Code, Box Type Code, Plant Code',
            'The codes: Part No., Part Color Code, Box Type Code, Plant Code have already been taken.',
            implode(',', [$orderPointControlAttributes['part_code'], $orderPointControlAttributes['part_color_code'], $orderPointControlAttributes['box_type_code'], $orderPointControlAttributes['plant_code']]));
    }

    public function test_order_point_control_import_with_fail_rules_validate()
    {
        $orderPointControl = OrderPointControl::factory()->sequence(fn($sequence) => [
            'part_code' => null,
            'part_color_code' => null,
            'standard_stock' => null,
            'ordering_lot' => null,
            'box_type_code' => null,
            'plant_code' => strtoupper(Str::random(10))
        ])->make();
        $orderPointControlAttributes = $orderPointControl->getAttributes();

        $fileNew = $this->createFileImport($orderPointControlAttributes);

        $response = $this->service->processDataImport($this->typeImportClass, $fileNew);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertArrayHasKey('link_download_error', $response);
        $this->assertStringContainsString(env('AWS_URL'), $response['link_download_error']);
        $this->assertIsArray($response['rows']);
        $index = 0;
        foreach (OrderPointControlImport::MAP_HEADING_ROW as $key => $attribute) {
            if ($key == 'plant_code') {
                $errorMessage = "The $attribute must not be greater than 5 characters.";
            } else {
                $errorMessage = "The $attribute field is required.";
            }
            $this->assertEquals([
                'line' => 4,
                'attribute' => $attribute,
                'errors' => $errorMessage,
                'value' => $key == 'plant_code' ? $orderPointControlAttributes['plant_code'] : ''
            ], $response['rows'][array_search($attribute, array_column($response['rows'], 'attribute'))]);
            $index++;
        }
        $this->assertDatabaseMissing('order_point_controls', Arr::only($orderPointControlAttributes, $orderPointControl->getFillable()));
    }

    private function createCorrectData($orderPointControlAttributes, $partColor = true, $boxType = true, $plant = true)
    {
        if ($partColor) {
            PartColor::factory()->sequence(fn($sequence) => [
                'code' => $orderPointControlAttributes['part_color_code'],
                'part_code' => $orderPointControlAttributes['part_code'],
                'plant_code' => $orderPointControlAttributes['plant_code']
            ])->create();
        }

        if ($boxType) {
            BoxType::factory()->sequence(fn($sequence) => [
                'part_code' => $orderPointControlAttributes['part_code'],
                'code' => $orderPointControlAttributes['box_type_code'],
                'plant_code' => $orderPointControlAttributes['plant_code']
            ])->create();
        }

        if ($plant) {
            Plant::factory()->sequence(fn($sequence) => [
                'code' => $orderPointControlAttributes['plant_code'],
            ])->create();
        }
    }

    private function createFileImport(array $orderPointControlAttributes = [], array $headingsClass = [], array $dataContent = [])
    {
        if (empty($dataContent)) {
            foreach ($this->dataContent as $key) {
                $data[$key] = $orderPointControlAttributes[$key];
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
