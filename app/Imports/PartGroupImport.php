<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\PartGroup;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class PartGroupImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'part_group_code',
        'part_group_description',
        'ordering_lead_time_week',
        'ordering_cycle',
        'delivery_lead_time_date'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'Part Group Code',
        'description' => 'Part Group Description',
        'lead_time' => 'Ordering Lead Time Week',
        'ordering_cycle' => 'Ordering cycle',
        'delivery_lead_time' => 'Delivery Lead Time Date'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Part Group Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The Part Group Code have already been taken.';

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
        $part_group_data = [
            'code' => strtoupper($data['part_group_code']),
            'description' => $data['part_group_description'],
            'lead_time' => $data['ordering_lead_time_week'],
            'ordering_cycle' => $data['ordering_cycle'],
            'delivery_lead_time' => $data['delivery_lead_time_date'] ?: null,
        ];

        $this->uniqueData[$index] = [$part_group_data['code']];

        return $part_group_data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:2',
            'description' => 'required|string|max:30',
            'lead_time' => 'required|integer|min:1|max:999',
            'ordering_cycle' => 'required|integer|min:1|max:9',
            'delivery_lead_time' => 'nullable|integer|min:1|max:99',
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
        ImportHelper::checkUniqueData($this->uniqueData, PartGroup::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        PartGroup::query()->insert($rows);
    }
}
