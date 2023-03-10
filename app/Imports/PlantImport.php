<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class PlantImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;
    public const HEADING_ROW = [
        'plant_code',
        'plant_code_description'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'Plant Code',
        'description' => 'Plant Code Description'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The Plant Code have already been taken.';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);
        $plant_data = [
            'code' => strtoupper($data['plant_code']),
            'description' => $data['plant_code_description']
        ];

        $this->uniqueData[$index] = [$plant_data['code']];

        return $plant_data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:5',
            'description' => 'required|string|max:255',
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
        ImportHelper::checkUniqueData($this->uniqueData, Plant::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        Plant::query()->insert($rows);
    }
}
