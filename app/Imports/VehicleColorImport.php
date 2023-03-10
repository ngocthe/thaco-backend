<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\VehicleColor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class VehicleColorImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'vehicle_color_code',
        'vehicle_color_type',
        'vehicle_color_name',
        'ecn_no_in',
        'ecn_no_out',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'Vehicle Color Code',
        'type' => 'Vehicle Color Type',
        'name' => 'Vehicle Color Name',
        'ecn_in' => 'ECN No. In',
        'ecn_out' => 'ECN No. Out',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code', 'ecn_in', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Vehicle Color Code, ECN No. In, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Vehicle Color Code, ECN No. In, Plant Code have already been taken.';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var array
     */
    protected array $plantCodes = [];

    /**
     * @var array
     */
    protected array $encNoInCodes = [];

    /**
     * @var array
     */
    protected array $encNoOutCodes = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $vehicleColorData = [
            'code' => strtoupper($data['vehicle_color_code']),
            'type' => $data['vehicle_color_type'],
            'name' => $data['vehicle_color_name'],
            'ecn_in' => strtoupper($data['ecn_no_in']),
            'ecn_out' => strtoupper($data['ecn_no_out'] ?: null),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($vehicleColorData['plant_code']) {
            $this->plantCodes[$index] = $vehicleColorData['plant_code'];
            $this->encNoInCodes[$index] = [$vehicleColorData['ecn_in'], $vehicleColorData['plant_code']];
            if ($vehicleColorData['ecn_out']) {
                $this->encNoOutCodes[$index] = [$vehicleColorData['ecn_out'], $vehicleColorData['plant_code']];
            }
            $this->uniqueData[$index] = [$vehicleColorData['code'], $vehicleColorData['ecn_in'], $vehicleColorData['plant_code']];
        }
        return $vehicleColorData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|min:1|max:4',
            'type' => 'required|alpha_num_dash|in:EXT,INT',
            'name' => 'required|string|max:20',
            'ecn_in' => 'required|alpha_num_dash|min:7|max:10',
            'ecn_out' => 'nullable|alpha_num_dash|min:7|max:10',
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

        ImportHelper::referenceCheckEcnCode($this->encNoInCodes, $this->encNoOutCodes, $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, VehicleColor::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        VehicleColor::query()->insert($rows);

    }
}
