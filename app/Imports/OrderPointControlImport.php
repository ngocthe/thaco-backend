<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\OrderPointControl;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class OrderPointControlImport extends BaseImport implements SkipsEmptyRows, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    public const HEADING_ROW = [
        'part_no',
        'part_color_code',
        'standard_stock_of_part_box',
        'ordering_lot_number_of_boxes',
        'box_type_code',
        'plant_code',
    ];

    public const MAP_HEADING_ROW = [
        'part_code' => 'Part No.',
        'part_color_code' => 'Part Color Code',
        'standard_stock' => 'Standard Stock of Part Box',
        'ordering_lot' => 'Ordering Lot Number of Boxes',
        'box_type_code' => 'Box Type Code',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = [
        'part_code',
        'part_color_code',
        'box_type_code',
        'plant_code'
    ];
    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Part No., Part Color Code, Box Type Code, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Part No., Part Color Code, Box Type Code, Plant Code have already been taken.';

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
    protected array $boxTypeCodes = [];

    /**
     * @var array
     */
    public array $dataByIndex = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $orderPointControlData = [
            'row' => $index,
            'part_code' => strtoupper($data['part_no']),
            'part_color_code' => strtoupper($data['part_color_code']),
            'box_type_code' => strtoupper($data['box_type_code']),
            'standard_stock' => $data['standard_stock_of_part_box'],
            'ordering_lot' => $data['ordering_lot_number_of_boxes'],
            'plant_code' => strtoupper($data['plant_code']),
        ];
        if ($orderPointControlData['plant_code']) {
            if ($orderPointControlData['part_code'] != '' && $orderPointControlData['part_color_code'] != '') {
                $this->partColorCodes[$index] = [$orderPointControlData['part_code'], $orderPointControlData['part_color_code'], $orderPointControlData['plant_code']];
            }
            if ($orderPointControlData['part_code'] != '' && $orderPointControlData['box_type_code'] != '' && $orderPointControlData['plant_code'] != '') {
                $this->boxTypeCodes[$index] = [$orderPointControlData['part_code'], $orderPointControlData['box_type_code'], $orderPointControlData['plant_code']];
            }
            $this->plantCodes[$index] = $orderPointControlData['plant_code'];
            $this->uniqueData[$index] = [
                $orderPointControlData['part_code'],
                $orderPointControlData['part_color_code'],
                $orderPointControlData['box_type_code'],
                $orderPointControlData['plant_code']
            ];
            $this->dataByIndex[$index] = $orderPointControlData;
        }
        return $orderPointControlData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'part_code' => 'required|alpha_num_dash|max:10',
            'part_color_code' => 'required|alpha_num_dash|max:2',
            'box_type_code' => 'required|alpha_num_dash|max:5',
            'standard_stock' => 'required|integer|min:1|max:999',
            'ordering_lot' => 'required|integer|min:1|max:99',
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
        ImportHelper::referenceCheckPartAndPartColor($this->partColorCodes, $this->failures,);
        ImportHelper::referenceCheckPartAndBoxType($this->boxTypeCodes, $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, OrderPointControl::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (!count($this->failures)) {
            $rows = [];
            $collection = $collection->toArray();
            $loggedId = auth()->id();
            $now = Carbon::now();
            foreach ($collection as $item) {
                unset($item['row']);
                $rows[] = array_merge($item, [
                    'created_by' => $loggedId,
                    'updated_by' => $loggedId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (count($rows))
                OrderPointControl::query()->insert($rows);
        }
    }
}
