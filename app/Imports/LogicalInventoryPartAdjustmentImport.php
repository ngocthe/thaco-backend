<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Services\LogicalInventoryPartAdjustmentService;
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

class LogicalInventoryPartAdjustmentImport extends BaseImport implements SkipsEmptyRows, SkipsOnFailure, SkipsOnError, WithChunkReading
{
    use Importable, SkipsFailures, SkipsErrors;

    public const HEADING_ROW = [
        'part_no',
        'part_color_code',
        'adjustment_quantity',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'part_code' => 'Part No.',
        'part_color_code' => 'Part Color Code',
        'adjustment_quantity' => 'Adjustment Quantity',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = [
        'part_code',
        'part_color_code',
        'plant_code'
    ];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Part No., Part Color Code, Plant Code';

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

        $adjustmentData = [
            'row' => $index,
            'part_code' => strtoupper($data['part_no']),
            'part_color_code' => strtoupper($data['part_color_code']),
            'adjustment_quantity' => $data['adjustment_quantity'],
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($adjustmentData['plant_code']) {
            $this->partColorCodes[$index] = [$adjustmentData['part_code'], $adjustmentData['part_color_code'], $adjustmentData['plant_code']];
            $this->plantCodes[$index] = $adjustmentData['plant_code'];
            $this->uniqueData[$index] = [];
            foreach ($this->uniqueKeys as $key) {
                $this->uniqueData[$index][] = $adjustmentData[$key];
            }
            $this->dataByIndex[$index] = $adjustmentData;
        }
        return $adjustmentData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'part_code' => 'required|alpha_num_dash|max:10',
            'part_color_code' => 'required|alpha_num_dash|max:2',
            'adjustment_quantity' => 'required|integer|min:-9999|max:9999|not_in:0',
            'plant_code' => 'required|alpha_num_dash|max:5'
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'adjustment_quantity.not_in' => 'The Adjustment Quantity must be an integer.',
            'adjustment_quantity.min' => 'The Adjustment Quantity must be at least :min.'
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
        list($rowsIgnore) = $this->validateData();
        $collection = $collection->toArray();

        $rowsImport = [];
        foreach ($collection as $item) {
            $row = $item['row'];
            if (!in_array($row, $rowsIgnore)) {
                $rowsImport[$row] = $item;
            }
        }
        if (count($rowsImport)) {
            DB::beginTransaction();
            try {
                (new LogicalInventoryPartAdjustmentService())->insertBulk($rowsImport, $this->uniqueKeys, $this->uniqueData);
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error($exception);
            }
        }
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
        ImportHelper::referenceCheckPartAndPartColor($this->partColorCodes, $this->failures, false);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures, false);

        $rowsIgnore = ImportHelper::getRowsIgnore($failuresInValidate, $this->failures);
        // remove data invalid by key
        foreach ($rowsIgnore as $key) {
            unset($this->uniqueData[$key]);
        }
        return [$rowsIgnore, $failuresInValidate];
    }
}
