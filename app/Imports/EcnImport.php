<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Ecn;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

/**
 */
class EcnImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'ecn_no',
        'ecn_page_number',
        'ecn_line_number',
        'ecn_description',
        'mandatory_level',
        'production_interchangeability',
        'service_interchangeability',
        'ecn_released_party',
        'ecn_released_date',
        'planned_inspection_line_off_effective_date',
        'actual_inspection_line_off_effective_date',
        'planned_packing_effective_date',
        'actual_packing_effective_date',
        'first_implementation_vin',
        'first_implementation_ckd_contract_no',
        'plant_code',
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'ECN No.',
        'page_number' => 'ECN Page Number',
        'line_number' => 'ECN Line Number',
        'description' => 'ECN Description',
        'mandatory_level' => 'Mandatory Level',
        'production_interchangeability' => 'Production Interchangeability',
        'service_interchangeability' => 'Service Interchangeability',
        'released_party' => 'ECN Released Party',
        'released_date' => 'ECN Released Date',
        'planned_line_off_date' => 'Planned Inspection Line Off Effective Date',
        'actual_line_off_date' => 'Actual Inspection Line Off Effective Date',
        'planned_packing_date' => 'Planned Packing Effective Date',
        'actual_packing_date' => 'Actual Packing Effective Date',
        'vin' => 'First Implementation VIN',
        'complete_knockdown' => 'First Implementation CKD Contract No.',
        'plant_code' => 'Plant Code'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'ECN No., Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: ECN No., Plant Code have already been taken.';

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
    protected array $orderNoInNoOut = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);
        $ecn_data = [
            'code' => strtoupper($data['ecn_no']),
            'page_number' => $data['ecn_page_number'],
            'line_number' => $data['ecn_line_number'],
            'description' => $data['ecn_description'],
            'mandatory_level' => $data['mandatory_level'],
            'production_interchangeability' => strtoupper($data['production_interchangeability']),
            'service_interchangeability' => strtoupper($data['service_interchangeability']),
            'released_party' => strtoupper($data['ecn_released_party']),
            'released_date' => $this->excelToDate($data['ecn_released_date']),
            'planned_line_off_date' => $this->excelToDate($data['planned_inspection_line_off_effective_date']),
            'actual_line_off_date' => $this->excelToDate($data['actual_inspection_line_off_effective_date']),
            'planned_packing_date' => $this->excelToDate($data['planned_packing_effective_date']),
            'actual_packing_date' => $this->excelToDate($data['actual_packing_effective_date']),
            'vin' => strtoupper($data['first_implementation_vin']),
            'complete_knockdown' => strtoupper($data['first_implementation_ckd_contract_no']),
            'plant_code' => strtoupper($data['plant_code'])
        ];

        $this->plantCodes[$index] = $ecn_data['plant_code'];
        $this->uniqueData[$index] = [$ecn_data['code'], $ecn_data['plant_code']];
        return $ecn_data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|min:7|max:10',
            'page_number' => 'required|integer|min:1|max:999',
            'line_number' => 'required|integer|min:1|max:999',
            'description' => 'required|string|max:30',
            'mandatory_level' => 'required|in:M,N',
            'production_interchangeability' => 'nullable|alpha_num_dash|max:1',
            'service_interchangeability' => 'nullable|alpha_num_dash|max:1',
            'released_party' => 'nullable|alpha_num_dash|max:5',
            'released_date' => 'nullable|date_format:d/m/Y',
            'planned_line_off_date' => 'nullable|date_format:d/m/Y',
            'actual_line_off_date' => 'nullable|date_format:d/m/Y',
            'planned_packing_date' => 'nullable|date_format:d/m/Y',
            'actual_packing_date' => 'nullable|date_format:d/m/Y',
            'vin' => 'nullable|alpha_num_dash|max:17',
            'complete_knockdown' => 'nullable|alpha_num_dash|max:13',
            'plant_code' => 'required|alpha_num_dash|max:5',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'released_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'planned_line_off_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'actual_line_off_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'planned_packing_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'actual_packing_date.date_format' => 'The :attribute does not match the format dd/mm/yyyy'
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

        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, Ecn::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection, [
            'released_date' => 'd/m/Y',
            'planned_line_off_date' => 'd/m/Y',
            'actual_line_off_date' => 'd/m/Y',
            'planned_packing_date' => 'd/m/Y',
            'actual_packing_date' => 'd/m/Y'
        ]);
        Ecn::query()->insert($rows);

    }

}
