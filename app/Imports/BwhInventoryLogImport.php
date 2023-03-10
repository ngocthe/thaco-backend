<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\InTransitInventoryLog;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Services\BwhInventoryLogService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BwhInventoryLogImport extends BaseImport implements SkipsEmptyRows, SkipsOnFailure, SkipsOnError, WithChunkReading
{
    use Importable, SkipsFailures, SkipsErrors;

    public const HEADING_ROW = [
        'contract_no',
        'invoice_no',
        'bl_no',
        'container_no',
        'case_no',
        'date_container_received',
        'date_container_devanned',
        'date_case_stored',
        'location_code',
        'warehouse_code',
        'date_case_shipped_to_upkwh',
        'plant_code',
        'defect_status'
    ];

    public const MAP_HEADING_ROW = [
        'contract_code' => 'Contract No.',
        'invoice_code' => 'Invoice No.',
        'bill_of_lading_code' => 'B/L No.',
        'container_code' => 'Container No.',
        'case_code' => 'Case No.',
        'container_received' => 'Date Container Received',
        'devanned_date' => 'Date Container Devanned',
        'stored_date' => 'Date Case Stored',
        'warehouse_location_code' => 'Location Code',
        'warehouse_code' => 'Warehouse Code',
        'shipped_date' => 'Date Case shipped to UPKWH',
        'plant_code' => 'Plant Code',
        'defect_id' => 'Defect Status'
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
        'plant_code'
    ];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Contract No., Invoice No., B/L No., Container No., Case No., Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Contract No., Invoice No., B/L No., Container No., Case No., Plant Code have already been taken.';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var array
     */
    protected array $warehouseCodes = [];

    /**
     * @var array
     */
    protected array $warehouseLocationCodes = [];

    /**
     * @var array
     */
    protected array $warehouseAndLocationCodes = [];

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

        $bwhInventoryLogData = [
            'row' => $index,
            'contract_code' => strtoupper($data['contract_no']),
            'invoice_code' => strtoupper($data['invoice_no']),
            'bill_of_lading_code' => strtoupper($data['bl_no']),
            'container_code' => strtoupper($data['container_no']),
            'case_code' => strtoupper($data['case_no']),
            'container_received' => $this->excelToDate($data['date_container_received']),
            'devanned_date' => $this->excelToDate($data['date_container_devanned']),
            'stored_date' => $this->excelToDate($data['date_case_stored']),
            'warehouse_code' => strtoupper($data['warehouse_code'] ?: null),
            'warehouse_location_code' => strtoupper($data['location_code'] ?: null),
            'shipped_date' => $this->excelToDate($data['date_case_shipped_to_upkwh']),
            'plant_code' => strtoupper($data['plant_code']),
            'defect_id' => strtoupper($data['defect_status']) ?: null,
        ];
        if ($bwhInventoryLogData['plant_code']) {
            if ($bwhInventoryLogData['warehouse_location_code'] != '' && $bwhInventoryLogData['warehouse_code'] != '') {
                $this->warehouseAndLocationCodes[$index] = [
                    $bwhInventoryLogData['warehouse_location_code'],
                    $bwhInventoryLogData['warehouse_code'],
                    $bwhInventoryLogData['plant_code']
                ];
            } else {
                if ($bwhInventoryLogData['warehouse_code'] != '') {
                    $this->warehouseCodes[$index] = [$bwhInventoryLogData['warehouse_code'], $bwhInventoryLogData['plant_code']];
                }
                if ($bwhInventoryLogData['warehouse_location_code'] != '') {
                    $this->warehouseLocationCodes[$index] = [$bwhInventoryLogData['warehouse_location_code'], $bwhInventoryLogData['plant_code']];
                }
            }
            $this->plantCodes[$index] = $bwhInventoryLogData['plant_code'];
            $this->uniqueData[$index] = [];
        }
        foreach ($this->uniqueKeys as $key) {
            $this->uniqueData[$index][] = $bwhInventoryLogData[$key];
        }

        $this->excelDateValidateAfterFields[$index] = [
            'container_received' => $bwhInventoryLogData['container_received'],
            'devanned_date' => $bwhInventoryLogData['devanned_date'],
            'stored_date' => $bwhInventoryLogData['stored_date'],
            'shipped_date' => $bwhInventoryLogData['shipped_date'],
        ];

        $this->dataByIndex[$index] = $bwhInventoryLogData;
        return $bwhInventoryLogData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'contract_code' => 'required|max:9|alpha_num_dash',
            'invoice_code' => 'required|max:10|alpha_num_dash',
            'bill_of_lading_code' => 'required|max:13|alpha_num_dash',
            'container_code' => 'required|max:11|alpha_num_dash',
            'case_code' => 'required|max:2|alpha_num_dash',
            'container_received' => 'required|date_format:d/m/Y|before_or_equal:today',
            'devanned_date' => 'required|date_format:d/m/Y|before_or_equal:today',
            'stored_date' => 'required|date_format:d/m/Y|before_or_equal:today',
            'warehouse_location_code' => 'nullable|alpha_num_dash|max:8',
            'warehouse_code' => 'nullable|required_with:warehouse_location_code|alpha_num_dash|max:8',
            'shipped_date' => 'nullable|date_format:d/m/Y|before_or_equal:today',
            'plant_code' => 'required|alpha_num_dash|max:5',
            'defect_id' => 'nullable|string|in:W,D,X,S',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'container_received.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'devanned_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'stored_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'shipped_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',

            'container_received.before_or_equal' => 'The :attribute must not be greater than today',
            'devanned_date.before_or_equal' => 'The :attribute must not be greater than today',
            'stored_date.before_or_equal' => 'The :attribute must not be greater than today',
            'shipped_date.before_or_equal' => 'The :attribute must not be greater than today',
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
        list($dataGroupByKey, $rowsIgnore) = $this->validateData();
        $collection = $collection->toArray();

        $rowsImport = [];
        foreach ($collection as $item) {
            $row = $item['row'];
            if (!in_array($row, $rowsIgnore)) {
                $rowsImport[$row] = $item;
            }
        }

        DB::beginTransaction();
        try {
            if (count($dataGroupByKey)) {
                (new BwhInventoryLogService())->insertOrUpdateBulk($rowsImport, $dataGroupByKey, $this->uniqueKeys,
                    $this->uniqueData, $this->failures);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }
        DB::commit();
    }

    /**
     * @return array
     * @throws ValidationException
     */
    protected function validateData(): array
    {
        $duplicate = ImportHelper::findDuplicateInMultidimensional($this->uniqueData);
        $failuresInValidate = [];
        if (count($duplicate)) {
            ImportHelper::handleDuplicateError($duplicate, $this->uniqueAttributes, $this->failures, false);
        }
        BwhInventoryLogService::validationDatePairs($this->excelDateValidateAfterFields, $this->failures);
        if (count($this->warehouseCodes)) {
            ImportHelper::referenceCheckWarehouse($this->warehouseCodes, $this->failures, Warehouse::TYPE_BWH, false);
        }

        if (count($this->warehouseLocationCodes)) {
            ImportHelper::referenceCheckDataPair($this->warehouseLocationCodes, ['code', 'plant_code'], WarehouseLocation::class,
                'Location Code, Plant Code',
                'Location Code, Plant Code are not linked together.',
                $this->failures,
                false);
        }

        if (count($this->warehouseAndLocationCodes)) {
            ImportHelper::referenceCheckLocationCode($this->warehouseAndLocationCodes, $this->failures);
        }
        $rowsIgnore = ImportHelper::getRowsIgnore($failuresInValidate, $this->failures);

        // remove data invalid by key
        foreach ($rowsIgnore as $key) {
            unset($this->uniqueData[$key]);
        }

        $dataGroupByKey = [];
        if (count($this->uniqueData)) {
            $dataGroupByKey = ImportHelper::checkAndGetDataInRefTable($this->uniqueKeys, $this->uniqueData,
                InTransitInventoryLog::class,
                'There is no corresponding data in the table in transit inventory',
                $this->uniqueAttributes, ['partInfo'], $rowsIgnore, $this->failures
            );
        }

        return [$dataGroupByKey, $rowsIgnore];
    }
}
