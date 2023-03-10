<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Bom;
use App\Models\Msc;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class BomImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'msc',
        'shop_code',
        'part_no',
        'part_color_code',
        'quantity_per_unit',
        'part_remarks',
        'ecn_no_in',
        'ecn_no_out',
        'plant_code'
    ];

    public const MAP_HEADING_ROW = [
        'msc_code' => 'MSC',
        'shop_code' => 'Shop Code',
        'part_code' => 'Part No.',
        'part_color_code' => 'Part Color Code',
        'quantity' => 'Quantity per Unit',
        'part_remarks' => 'Part Remarks',
        'ecn_in' => 'ECN No. In',
        'ecn_out' => 'ECN No. Out',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['msc_code', 'shop_code', 'part_code', 'part_color_code', 'ecn_in', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'MSC, Shop Code, Part No., Part Color Code, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: MSC, Shop Code, Part No., Part Color Code, Plant Code have already been taken.';

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
    protected array $partColorCodes = [];

    /**
     * @var array
     */
    protected array $mscCodes = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $bom_data = [
            'msc_code' => strtoupper($data['msc']),
            'shop_code' => strtoupper($data['shop_code']),
            'part_code' => strtoupper($data['part_no']),
            'part_color_code' => strtoupper($data['part_color_code']),
            'quantity' => $data['quantity_per_unit'],
            'part_remarks' => $data['part_remarks'],
            'ecn_in' => strtoupper($data['ecn_no_in']),
            'ecn_out' => strtoupper($data['ecn_no_out'] ?: null),
            'plant_code' => strtoupper($data['plant_code'])
        ];
        if ($bom_data['plant_code']) {
            $this->mscCodes[$index] = [$bom_data['msc_code'], $bom_data['plant_code']];
            if ($bom_data['part_color_code'] != 'XX') {
                $this->partColorCodes[$index] = [$bom_data['part_code'], $bom_data['part_color_code'], $bom_data['plant_code']];
            }
            $this->encNoInCodes[$index] = [$bom_data['ecn_in'], $bom_data['plant_code']];
            if ($bom_data['ecn_out'])
                $this->encNoOutCodes[$index] = [$bom_data['ecn_out'], $bom_data['plant_code']];
            $this->plantCodes[$index] = $bom_data['plant_code'];
        }
        $this->uniqueData[$index] = [
            $bom_data['msc_code'],
            $bom_data['shop_code'],
            $bom_data['part_code'],
            $bom_data['part_color_code'],
            $bom_data['ecn_in'],
            $bom_data['plant_code']
        ];
        return $bom_data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'msc_code' => 'required|alpha_num_dash|max:7',
            'shop_code' => 'required|alpha_num_dash|max:3',
            'part_code' => 'required|alpha_num_dash|max:10',
            'part_color_code' => 'required|alpha_num_dash|max:2',
            'quantity' => 'required|integer|min:1|max:99999',
            'part_remarks' => 'nullable|string|max:50',
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
        ImportHelper::referenceCheckMsc($this->mscCodes, $this->failures);
        ImportHelper::referenceCheckPartAndPartColor($this->partColorCodes, $this->failures);
        ImportHelper::referenceCheckEcnCode($this->encNoInCodes, $this->encNoOutCodes, $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, Bom::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection);
        Bom::query()->insert($rows);

    }
}
