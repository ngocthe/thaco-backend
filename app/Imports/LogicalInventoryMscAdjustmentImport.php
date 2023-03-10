<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Bom;
use App\Models\LogicalInventoryMscAdjustment;
use App\Models\Msc;
use App\Models\VehicleColor;
use App\Services\LogicalInventoryMscAdjustmentService;
use Carbon\Carbon;
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

class LogicalInventoryMscAdjustmentImport extends BaseImport implements SkipsEmptyRows, SkipsOnFailure, SkipsOnError, WithChunkReading
{
    use Importable, SkipsFailures, SkipsErrors;

    public const HEADING_ROW = [
        'msc_code',
        'exterior_color_code',
        'adjustment_quantity',
        'production_date',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'msc_code' => 'MSC Code',
        'vehicle_color_code' => 'Exterior Color Code',
        'adjustment_quantity' => 'Adjustment Quantity',
        'production_date' => 'Production Date',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = [
        'msc_code',
        'vehicle_color_code',
        'production_date',
        'plant_code'
    ];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'MSC Code, Exterior Color Code, Production Date, Plant Code';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var array
     */
    protected array $mscCode = [];

    /**
     * @var array
     */
    protected array $vehicleColorCode = [];

    /**
     * @var array
     */
    protected array $plantCodes = [];

    /**
     * @var array
     */
    protected array $dataByMsc = [];

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
            'msc_code' => strtoupper($data['msc_code']),
            'vehicle_color_code' => strtoupper($data['exterior_color_code']),
            'adjustment_quantity' => $data['adjustment_quantity'],
            'production_date' => $this->excelToDate($data['production_date']),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($adjustmentData['plant_code']) {
            $this->mscCode[$index] = [$adjustmentData['msc_code'], $adjustmentData['plant_code']];
            $this->vehicleColorCode[$index] = [$adjustmentData['vehicle_color_code'], $adjustmentData['plant_code']];
            $this->plantCodes[$index] = $adjustmentData['plant_code'];
            $this->uniqueData[$index] = [];
            foreach ($this->uniqueKeys as $key) {
                $this->uniqueData[$index][] = $adjustmentData[$key];
            }
            $this->dataByMsc[$index] = [$adjustmentData['msc_code'], $adjustmentData['plant_code']];
        }
        $this->dataByIndex[$index] = $adjustmentData;
        return $adjustmentData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'msc_code' => 'required|alpha_num_dash|max:7',
            'adjustment_quantity' => 'required|integer|min:-9999|max:9999|not_in:0',
			'vehicle_color_code' => 'required|alpha_num_dash|max:4',
			'production_date' => 'required|date_format:d/m/Y|before:tomorrow',
			'plant_code' => 'required|alpha_num_dash|max:5',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'adjustment_quantity.not_in' => 'The Adjustment Quantity must be an integer.',
            'production_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
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
                $key = $item['msc_code'] . '-' . $item['plant_code'];
                $item['production_date'] = Carbon::createFromFormat('d/m/Y', $item['production_date'])->format('Y-m-d');
                $rowsImport[$key][] = $item;
            }
        }
        if (count($rowsImport)) {
            DB::beginTransaction();
            try {
                $this->handleImportMultiMsc($rowsImport, $this->dataByMsc);
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

        ImportHelper::referenceCheckMsc($this->mscCode, $this->failures);
        ImportHelper::referenceCheckDataPair($this->vehicleColorCode, ['code', 'plant_code'], VehicleColor::class,
            'Exterior Color Code, Plant Code',
            'Exterior Color Code, Plant Code are not linked together.',
            $this->failures,
            false);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures, false);

        $rowsIgnore = ImportHelper::getRowsIgnore($failuresInValidate, $this->failures);
        // remove data invalid by key
        foreach ($rowsIgnore as $key) {
            unset($this->uniqueData[$key]);
            unset($this->dataByMsc[$key]);
        }
        return [$rowsIgnore, $failuresInValidate];
    }

    /**
     * @param $rowsImport
     * @param $dataByMsc
     * @return void
     */
    private function handleImportMultiMsc($rowsImport, $dataByMsc)
    {
        $rowsInsert = [];
        $loggedId = auth()->id();
        $currentTime = Carbon::now()->toDateTimeString();
        foreach ($rowsImport as $key => $rows) {
            foreach ($rows as $row) {
                unset($row['row']);
                $rowsInsert[] = array_merge($row, [
                    'created_by' => $loggedId,
                    'updated_by' => $loggedId,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ]);
            }
        }

        LogicalInventoryMscAdjustment::query()->insert($rowsInsert);

        $boms = Bom::whereInMultiple(['msc_code', 'plant_code'], $dataByMsc)
            ->select(['msc_code', 'part_code', 'part_color_code', 'quantity', 'plant_code'])
            ->get()
            ->toArray();
        $partAndPartColorData = [];
        $partAndPartColorQuantity = [];

        $logicalInventoryMscAdjustmentService = new LogicalInventoryMscAdjustmentService();
        foreach ($boms as $bom) {
            $mscKey = $bom['msc_code'] . '-' . $bom['plant_code'];
            $rowImport = $rowsImport[$mscKey];
            foreach ($rowImport as $row) {
                $partColorCode = $logicalInventoryMscAdjustmentService->getPartColorCode($bom, $row);
                $partAndPartColorData[] = [
                    'production_date' => $row['production_date'],
                    'part_code' => $bom['part_code'],
                    'part_color_code' => $partColorCode,
                    'plant_code' => $bom['plant_code']
                ];
                $key = $bom['part_code'] . '-' . $partColorCode . '-' . $bom['plant_code']. '-' . $row['production_date'];
                $partAndPartColorQuantity[$key] = $bom['quantity'] * $row['adjustment_quantity'];
            }
        }

        if (count($partAndPartColorData)) {
            $logicalInventoryMscAdjustmentService->insertIntoLogicalInventoryBulk($partAndPartColorData, $partAndPartColorQuantity);
        }
    }
}
