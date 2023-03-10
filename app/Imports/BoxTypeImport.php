<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\BoxType;
use App\Models\Part;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class BoxTypeImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'part_no',
        'box_type_code',
        'box_type_description',
        'box_weight_gram',
        'size_of_box_x_mm',
        'size_of_box_y_mm',
        'size_of_box_z_mm',
        'part_quantity_in_box',
        'unit_of_measure',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'part_code' => 'Part No.',
        'code' => 'Box Type Code',
        'description' => 'Box Type Description',
        'weight' => 'Box Weight Gram',
        'width' => 'Size of Box X mm',
        'height' => 'Size of Box Y mm',
        'depth' => 'Size of Box Z mm',
        'quantity' => 'Part Quantity in Box',
        'unit' => 'Unit of Measure',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code', 'part_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Box Type Code, Part No., Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Box Type Code, Part No., Plant Code have already been taken.';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var array
     */
    protected array $partCodes = [];

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

        $boxTypeData = [
            'code' => strtoupper($data['box_type_code']),
            'part_code' => strtoupper($data['part_no']),
            'description' => $data['box_type_description'],
            'weight' => $data['box_weight_gram'],
            'width' => $data['size_of_box_x_mm'],
            'height' => $data['size_of_box_y_mm'],
            'depth' => $data['size_of_box_z_mm'],
            'quantity' => $data['part_quantity_in_box'],
            'unit' => strtoupper($data['unit_of_measure']),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($boxTypeData['plant_code']) {
            $this->partCodes[$index] = [$boxTypeData['part_code'], $boxTypeData['plant_code']];
            $this->plantCodes[$index] = $boxTypeData['plant_code'];
            $this->uniqueData[$index] = [$boxTypeData['code'], $boxTypeData['part_code'], $boxTypeData['plant_code']];
        }
        return $boxTypeData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:5',
            'part_code' => 'required|alpha_num_dash|max:10',
            'description' => 'required|string|max:20',
            'weight' => 'required|integer|min:1|max:9999',
            'width' => 'required|integer|min:1|max:9999',
            'height' => 'required|integer|min:1|max:9999',
            'depth' => 'required|integer|min:1|max:9999',
            'quantity' => 'required|integer|min:1|max:9999',
            'unit' => 'required|unit_of_measure',
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
        ImportHelper::referenceCheckPart($this->partCodes, $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, BoxType::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        BoxType::query()->insert($rows);

    }

}
