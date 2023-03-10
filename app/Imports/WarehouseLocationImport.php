<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class WarehouseLocationImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'warehouse_code',
        'location_code',
        'location_description',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'Location Code',
        'warehouse_code' => 'Warehouse Code',
        'description' => 'Location Description',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code', 'warehouse_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Location Code, Warehouse Code, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Location Code, Warehouse Code, Plant Code have already been taken.';

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
    protected array $plantCodes = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $locationData = [
            'code' => strtoupper($data['location_code']),
            'warehouse_code' => strtoupper($data['warehouse_code']),
            'description' => $data['location_description'],
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($locationData['plant_code']) {
            $this->warehouseCodes[$index] = [$locationData['warehouse_code'], $locationData['plant_code']];
            $this->plantCodes[$index] = $locationData['plant_code'];
            $this->uniqueData[$index] = [$locationData['code'], $locationData['warehouse_code'], $locationData['plant_code']];
        }
        return $locationData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:8',
            'warehouse_code' => 'required|alpha_num_dash|max:8',
            'description' => 'required|string|max:255',
            'plant_code' => 'required|alpha_num_dash|max:5',
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
        $duplicate = ImportHelper::findDuplicateInMultidimensional($this->uniqueData);
        if (count($duplicate)) {
            ImportHelper::handleDuplicateError($duplicate, $this->uniqueAttributes, $this->failures);
        }

        ImportHelper::referenceCheckWarehouse($this->warehouseCodes, $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, WarehouseLocation::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        WarehouseLocation::query()->insert($rows);

    }

}
