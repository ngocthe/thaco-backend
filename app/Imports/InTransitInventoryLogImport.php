<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\InTransitInventoryLog;
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
use Maatwebsite\Excel\Validators\Failure;

class InTransitInventoryLogImport extends BaseImport implements SkipsEmptyRows, SkipsOnFailure, SkipsOnError, WithChunkReading
{
    use Importable, SkipsFailures, SkipsErrors;

    public const HEADING_ROW = [
        'contract_no',
        'invoice_no',
        'bl_no',
        'container_no',
        'case_no',
        'part_no',
        'part_color_code',
        'box_type_code',
        'quantity_of_box',
        'part_quantity_in_box',
        'unit_of_measure',
        'procurement_supplier_code',
        'etd',
        'date_container_shipped',
        'eta',
        'plant_code',
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
        'etd' => 'ETD',
        'container_shipped' => 'Date Container Shipped',
        'eta' => 'ETA',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = [
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

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Contract No., Invoice No., B/L No., Container No., Case No., Part No., Part Color Code, Box Type Code, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Contract No., Invoice No., B/L No., Container No., Case No., Part No., Part Color Code, Box Type Code, Plant Code have already been taken.';

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
    protected array $excelDateValidateAfterFields = [];

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

        $inTransitLogData = [
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
            'etd' => $this->excelToDate($data['etd']),
            'container_shipped' => $this->excelToDate($data['date_container_shipped']),
            'eta' => $this->excelToDate($data['eta']),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($inTransitLogData['plant_code']) {
            if ($inTransitLogData['part_code'] != '' && $inTransitLogData['part_color_code'] != '') {
                $this->partColorCodes[$index] = [$inTransitLogData['part_code'], $inTransitLogData['part_color_code'], $inTransitLogData['plant_code']];
            }
            if ($inTransitLogData['part_code'] != '' && $inTransitLogData['box_type_code'] != '' && $inTransitLogData['plant_code'] != '') {
                $this->boxTypeCodes[$index] = [$inTransitLogData['part_code'], $inTransitLogData['box_type_code'], $inTransitLogData['plant_code']];
            }
            if ($inTransitLogData['supplier_code']) {
                $this->supplierCodes[$index] = $inTransitLogData['supplier_code'];
            }
            $this->plantCodes[$index] = $inTransitLogData['plant_code'];
        }
        $this->excelDateValidateAfterFields[$index] = [
            'etd' => $inTransitLogData['etd'],
            'eta' => $inTransitLogData['eta']
        ];

        $this->uniqueData[$index] = [];
        foreach ($this->uniqueKeys as $key) {
            $this->uniqueData[$index][] = $inTransitLogData[$key];
        }
        $this->dataByIndex[$index] = $inTransitLogData;
        return $inTransitLogData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'contract_code' => 'required|alpha_num_dash|max:9',
            'invoice_code' => 'required|alpha_num_dash|max:10',
            'bill_of_lading_code' => 'required|alpha_num_dash|max:13',
            'container_code' => 'required|alpha_num_dash|max:11',
            'case_code' => 'required|alpha_num_dash|max:2',
            'part_code' => 'required|alpha_num_dash|max:10',
            'part_color_code' => 'required|alpha_num_dash|max:2',
            'box_type_code' => 'required|alpha_num_dash|max:5',
            'box_quantity' => 'required|integer|min:1|max:9999',
            'part_quantity' => 'required|integer|min:1|max:9999',
            'unit' => 'required|unit_of_measure',
            'supplier_code' => 'required|alpha_num_dash|max:5',
            'etd' => 'required|date_format:d/m/Y',
            'container_shipped' => 'required|date_format:d/m/Y',
            'eta' => 'required|date_format:d/m/Y',
            'plant_code' => 'required|alpha_num_dash|max:5',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'etd.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'container_shipped.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'eta.date_format' => 'The :attribute does not match the format dd/mm/yyyy'
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
        foreach ($collection as $item) {
            $row = $item['row'];
            unset($item['row']);

            if (!in_array($row, $rowsIgnore)) {
                $data = array_merge($item, [
                    'etd' => $item['etd'] ? Carbon::createFromFormat('d/m/Y', $item['etd'])->toDateString() : null,
                    'container_shipped' => $item['container_shipped'] ? Carbon::createFromFormat('d/m/Y', $item['container_shipped'])->toDateString() : null,
                    'eta' => $item['eta'] ? Carbon::createFromFormat('d/m/Y', $item['eta'])->toDateString() : null,
                    'created_by' => $loggedId,
                    'updated_by' => $loggedId,
                    'deleted_at' => null
                ]);
                $rows[] = $data;
            }
        }

        if (count($rows))
            InTransitInventoryLog::query()
                ->upsert(
                    $rows,
                    $this->uniqueKeys,
                    [
                        'box_quantity',
                        'part_quantity',
                        'unit',
                        'supplier_code',
                        'etd',
                        'container_shipped',
                        'eta',
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
        ImportHelper::referenceCheckSupplier($this->supplierCodes, $this->failures, false);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures, false);
        foreach ($this->excelDateValidateAfterFields as $row => $dates) {
            if (!ImportHelper::__isGreaterThanOrEqualDate($dates['etd'], $dates['eta'])) {
                ImportHelper::__handleAfterDateError($this->failures, $row, $dates, 'eta', 'ETA',
                    'ETA must come after or equal ETD', false);
            }
        }
        return ImportHelper::getRowsIgnore([], $this->failures);
    }
}
