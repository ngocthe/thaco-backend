<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\VietnamSourceLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VietnamSourceLogLogImport extends BaseImport implements SkipsEmptyRows, SkipsOnFailure, SkipsOnError, WithChunkReading
{
    use Importable, SkipsFailures, SkipsErrors;

    public const HEADING_ROW = [
        "contract_no",
        "invoice_no",
        "bl_no",
        "container_no",
        "case_no",
        "part_no",
        "part_color_code",
        "box_type_code",
        "quantity_of_box",
        "part_quantity_in_box",
        "unit_of_measure",
        "procurement_supplier_code",
        "delivery_date",
        "plant_code",
    ];

    public const MAP_HEADING_ROW = [
        'contract_code' => 'Contract No.',
        'invoice_code' => 'Invoice No.',
        'bill_of_lading_code' => 'B/L No.',
        'container_code' => 'Container No.',
        'case_code' => 'Case No.',
        'part_code' => 'Part No.',
        'part_color_code' => 'Part Color Code',
        'box_type_code' => 'Box Type Code',
        'box_quantity' => 'Quantity of Box',
        'part_quantity' => 'Part Quantity in Box',
        'unit' => 'Unit of Measure',
        'supplier_code' => 'Procurement Supplier Code',
        'delivery_date' => 'Delivery Date',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = [
        'contract_code',
        'part_code',
        'part_color_code',
        'box_type_code',
        'plant_code'
    ];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Contract No., Part No., Part Color Code, Box Type Code, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Contract No., Part No., Part Color Code, Box Type Code, Plant Code have already been taken.';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var array
     */
    protected array $partColorCodes = [];

    /**
     * @var array
     */
    protected array $boxTypeCodes = [];

    /**
     * @var array
     */
    protected array $supplierCodes = [];

    /**
     * @var array
     */
    protected array $plantCodes = [];

    /**
     * @var array
     */
    public array $dataByIndex = [];

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $vnSourceData = [
            'row' => $index,
            'contract_code' => strtoupper($data['contract_no']),
            'invoice_code' => strtoupper($data['invoice_no']),
            'bill_of_lading_code' => strtoupper($data['bl_no']),
            'container_code' => strtoupper($data['container_no']),
            'case_code' => strtoupper($data['case_no']),
            'part_code' => strtoupper($data['part_no']),
            'part_color_code' => strtoupper($data['part_color_code']),
            'box_type_code' => strtoupper($data['box_type_code']),
            'box_quantity' => $data['quantity_of_box'],
            'part_quantity' => $data['part_quantity_in_box'],
            'unit' => strtoupper($data['unit_of_measure']),
            'supplier_code' => strtoupper($data['procurement_supplier_code']),
            'delivery_date' => $this->excelToDate( $data['delivery_date']),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($vnSourceData['plant_code']) {
            if ($vnSourceData['part_code'] != '' && $vnSourceData['part_color_code'] != '') {
                $this->partColorCodes[$index] = [$vnSourceData['part_code'], $vnSourceData['part_color_code'], $vnSourceData['plant_code']];
            }
            if ($vnSourceData['part_code'] != '' && $vnSourceData['box_type_code'] != '' && $vnSourceData['plant_code'] != '') {
                $this->boxTypeCodes[$index] = [$vnSourceData['part_code'], $vnSourceData['box_type_code'], $vnSourceData['plant_code']];
            }
            $this->supplierCodes[$index] = $vnSourceData['supplier_code'];
            $this->plantCodes[$index] = $vnSourceData['plant_code'];
        }
        $this->uniqueData[$index] = [];
        foreach ($this->uniqueKeys as $key) {
            $this->uniqueData[$index][] = $vnSourceData[$key];
        }
        $this->dataByIndex[$index] = $vnSourceData;
        return $vnSourceData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'contract_code' => 'required|alpha_num_dash|max:9',
            'invoice_code' => 'nullable|alpha_num_dash|max:10',
            'bill_of_lading_code' => 'nullable|alpha_num_dash|max:13',
            'container_code' => 'required|alpha_num_dash|max:11',
            'case_code' => 'nullable|alpha_num_dash|max:2',
            'part_code' => 'required|alpha_num_dash|max:10',
            'part_color_code' => 'required|alpha_num_dash|max:2',
            'box_type_code' => 'required|alpha_num_dash|max:5',
            'box_quantity' => 'required|integer|min:1|max:9999',
            'part_quantity' => 'required|integer|min:1|max:9999',
            'unit' => 'required|unit_of_measure',
            'supplier_code' => 'required|alpha_num_dash|max:5',
            'delivery_date' => 'required|date_format:d/m/Y',
            'plant_code' => 'required|alpha_num_dash|max:5',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'delivery_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy'
        ];
    }

    /**
     * @return array
     */
    public function customValidationAttributes(): array
    {
        return self::MAP_HEADING_ROW;
    }

    /**
     * @param Collection $collection
     * @return void
     * @throws ValidationException
     */
    public function collection(Collection $collection)
    {
        $rowsIgnore = $this->validateData();
        $rows = [];
        $collection = $collection->toArray();
        $loggedId = auth()->id();
        $now = Carbon::now();
        foreach ($collection as $item) {
            $row = $item['row'];
            unset($item['row']);

            if (!in_array($row, $rowsIgnore)) {
                $data = array_merge($item, [
                    'delivery_date' => $item['delivery_date'] ? Carbon::createFromFormat('d/m/Y', $item['delivery_date']) : null,
                    'created_by' => $loggedId,
                    'updated_by' => $loggedId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null
                ]);

                $rows[] = $data;
            }
        }

        if (count($rows))
            VietnamSourceLog::query()
                ->upsert(
                    $rows,
                    $this->uniqueKeys,
                    [
                        'invoice_code',
                        'bill_of_lading_code',
                        'container_code',
                        'case_code',
                        'box_quantity',
                        'part_quantity',
                        'unit',
                        'supplier_code',
                        'delivery_date',
                        'created_by',
                        'updated_by',
                        'deleted_at'
                    ]
                );

    }

    /**
     * @return array
     * @throws ValidationException
     */
    protected function validateData(): array
    {
        $duplicate = ImportHelper::findDuplicateInMultidimensional($this->uniqueData);
        if (count($duplicate)) {
            ImportHelper::handleDuplicateError($duplicate, $this->uniqueAttributes, $this->failures, false);
        }
        ImportHelper::referenceCheckPartAndPartColor($this->partColorCodes, $this->failures, false);
        ImportHelper::referenceCheckPartAndBoxType($this->boxTypeCodes, $this->failures, false);
        ImportHelper::referenceCheckSupplier($this->supplierCodes,  $this->failures, false);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures, false);
        return ImportHelper::getRowsIgnore([], $this->failures);
    }
}
