<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Procurement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class ProcurementImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'part_no',
        'part_color_code',
        'minimum_order_quantity',
        'standard_number_of_boxes',
        'parts_quantity_in_box',
        'unit_of_measure',
        'procurement_supplier_code',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'part_code' => 'Part No.',
        'part_color_code' => 'Part Color Code',
        'minimum_order_quantity' => 'Minimum Order Quantity',
        'standard_box_quantity' => 'Standard Number Of Boxes',
        'part_quantity' => 'Parts Quantity In Box',
        'unit' => 'Unit of Measure',
        'supplier_code' => 'Procurement Supplier Code',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['part_code', 'part_color_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Part No., Part Color Code, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Part No., Part Color Code, Plant Code have already been taken.';

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
    protected array $partColorCodes = [];

    /**
     * @var array
     */
    protected array $supplierCodes = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $procurement_data = [
            'part_code' => strtoupper($data['part_no']),
            'part_color_code' => strtoupper($data['part_color_code']),
            'minimum_order_quantity' => $data['minimum_order_quantity'],
            'standard_box_quantity' => $data['standard_number_of_boxes'],
            'part_quantity' => $data['parts_quantity_in_box'],
            'unit' => strtoupper($data['unit_of_measure']),
            'supplier_code' => strtoupper($data['procurement_supplier_code']),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($procurement_data['plant_code']) {
            $this->partColorCodes[$index] = [$procurement_data['part_code'], $procurement_data['part_color_code'], $procurement_data['plant_code']];
            $this->supplierCodes[$index] = $procurement_data['supplier_code'];
            $this->plantCodes[$index] = $procurement_data['plant_code'];
            $this->uniqueData[$index] = [$procurement_data['part_code'], $procurement_data['part_color_code'], $procurement_data['plant_code']];
        }
        return $procurement_data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'part_code' => 'required|alpha_num_dash|max:10',
            'part_color_code' => 'required|alpha_num_dash|max:2',
            'minimum_order_quantity' => 'required|integer|min:1|max:99999',
            'standard_box_quantity' => 'required|integer|min:1|max:9999',
            'part_quantity' => 'required|integer|min:1|max:9999',
            'unit' => 'required|unit_of_measure',
            'supplier_code' => 'required|alpha_num_dash|max:5',
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
        ImportHelper::referenceCheckPartAndPartColor($this->partColorCodes, $this->failures);
        ImportHelper::referenceCheckSupplier($this->supplierCodes,  $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, Procurement::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        Procurement::query()->insert($rows);
    }
}
