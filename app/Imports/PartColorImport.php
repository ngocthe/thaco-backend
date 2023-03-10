<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\PartColor;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class PartColorImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'part_no',
        'part_color_code',
        'part_color_name',
        'interior_color_condition',
        'exterior_color_condition',
        'ecn_no_in',
        'ecn_no_out',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'part_code' => 'Part No.',
        'code' => 'Part Color Code',
        'name' => 'Part Color Name',
        'interior_code' => 'Interior Color Condition',
        'vehicle_color_code' => 'Exterior Color Condition',
        'ecn_in' => 'ECN No. In',
        'ecn_out' => 'ECN No. Out',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['part_code', 'code', 'ecn_in', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Part No., Part Color Code, ECN No. In, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Part No., Part Color Code, ECN No. In, Plant Code have already been taken.';

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
     * @var array
     */
    protected array $interiorCodes = [];

    /**
     * @var array
     */
    protected array $vehicleColorCodes = [];

    /**
     * @var array
     */
    protected array $partCodes = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $partColorData = [
            'code' => strtoupper($data['part_color_code']),
            'part_code' => strtoupper($data['part_no']),
            'name' => $data['part_color_name'],
            'interior_code' => strtoupper($data['interior_color_condition'] ?: null),
            'vehicle_color_code' => strtoupper($data['exterior_color_condition'] ?: null),
            'ecn_in' => strtoupper($data['ecn_no_in']),
            'ecn_out' => strtoupper($data['ecn_no_out'] ?: null),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($partColorData['plant_code']) {
            $this->partCodes[$index] = [$partColorData['part_code'], $partColorData['plant_code']];
            if ($partColorData['interior_code'])
                $this->interiorCodes[$index] = [$partColorData['interior_code'], $partColorData['plant_code']];
            if ($partColorData['vehicle_color_code'])
                $this->vehicleColorCodes[$index] = [$partColorData['vehicle_color_code'], $partColorData['plant_code']];
            $this->encNoInCodes[$index] = [$partColorData['ecn_in'], $partColorData['plant_code']];
            if ($partColorData['ecn_out'])
                $this->encNoOutCodes[$index] = [$partColorData['ecn_out'], $partColorData['plant_code']];
            $this->plantCodes[$index] = $partColorData['plant_code'];
            $this->uniqueData[$index] = [$partColorData['part_code'], $partColorData['code'], $partColorData['ecn_in'], $partColorData['plant_code']];
        }
        return $partColorData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:2|not_in:XX,xx,xX,Xx',
            'part_code' => 'required|alpha_num_dash|max:10',
            'name' => 'required|string|max:20',
            'interior_code' => 'nullable|alpha_num_dash|max:4',
            'vehicle_color_code' => 'nullable|alpha_num_dash|max:4',
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
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'code.not_in' => 'The Part Color Code is invalid.',
        ];
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

        ImportHelper::referenceCheckPart($this->partCodes, $this->failures);
        ImportHelper::referenceCheckVehicleColorCode($this->interiorCodes, 'INT', 'Interior Color Condition', 'interior_code', $this->failures);
        ImportHelper::referenceCheckVehicleColorCode($this->vehicleColorCodes, 'EXT', 'Exterior Color Condition', 'vehicle_color_code', $this->failures);
        ImportHelper::referenceCheckEcnCode($this->encNoInCodes, $this->encNoOutCodes, $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, PartColor::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        PartColor::query()->insert($rows);

    }
}
