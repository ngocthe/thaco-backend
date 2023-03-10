<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Part;
use App\Models\PartGroup;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class PartImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'part_no',
        'part_name',
        'part_group',
        'ecn_no_in',
        'ecn_no_out',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'Part No.',
        'name' => 'Part Name',
        'group' => 'Part Group',
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
    protected string $uniqueAttributes = 'Part No., ECN No. In, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Part No., ECN No. In, Plant Code have already been taken.';

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
    protected array $partGroupCodes = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $partData = [
            'code' => strtoupper($data['part_no']),
            'name' => $data['part_name'],
            'group' => strtoupper($data['part_group']),
            'ecn_in' => strtoupper($data['ecn_no_in']),
            'ecn_out' => strtoupper($data['ecn_no_out'] ?: null),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($partData['plant_code']) {
            $this->plantCodes[$index] = $partData['plant_code'];
            $this->partGroupCodes[$index] = $partData['group'];
            $this->encNoInCodes[$index] = [$partData['ecn_in'], $partData['plant_code']];
            if ($partData['ecn_out'])
                $this->encNoOutCodes[$index] = [$partData['ecn_out'], $partData['plant_code']];
            $this->uniqueData[$index] = [$partData['code'], $partData['ecn_in'], $partData['plant_code']];
        }
        return $partData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:10',
            'name' => 'required|string|max:20',
            'group' => 'required|alpha_num_dash|max:2',
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
        ImportHelper::referenceCheckPartGroup($this->partGroupCodes, $this->failures);
        ImportHelper::referenceCheckEcnCode($this->encNoInCodes, $this->encNoOutCodes, $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, Part::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        Part::query()->insert($rows);

    }

}
