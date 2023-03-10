<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Msc;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class MscImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'msc',
        'msc_description',
        'interior_color_description',
        'car_line_name',
        'model_and_grade_name',
        'body_description',
        'engine_description',
        'tm_description',
        'plant_code',
        'msc_effective_date_in',
        'msc_effective_date_out'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'MSC',
        'description' => 'MSC Description',
        'interior_color' => 'Interior Color Description',
        'car_line' => 'Car-Line Name',
        'model_grade' => 'Model And Grade Name',
        'body' => 'Body Description',
        'engine' => 'Engine Description',
        'transmission' => 'TM Description',
        'plant_code' => 'Plant Code',
        'effective_date_in' => 'MSC Effective Date in',
        'effective_date_out' => 'MSC Effective Date out'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code', 'plant_code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'MSC, Plant Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: MSC, Plant Code have already been taken.';


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
    protected array $orderEffectiveDate = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);

        $msc_data = [
            'code' => strtoupper($data['msc']),
            'description' => $data['msc_description'],
            'interior_color' => $data['interior_color_description'],
            'car_line' => $data['car_line_name'],
            'model_grade' => $data['model_and_grade_name'],
            'body' => $data['body_description'],
            'engine' => $data['engine_description'],
            'transmission' => $data['tm_description'],
            'plant_code' => strtoupper($data['plant_code']),
            'effective_date_in' => $this->excelToDate($data['msc_effective_date_in']),
            'effective_date_out' => $this->excelToDate($data['msc_effective_date_out'])
        ];

        $this->plantCodes[$index] = $msc_data['plant_code'];
        $this->uniqueData[$index] = [$msc_data['code'], $msc_data['plant_code']];
        $this->orderEffectiveDate [$index] = [
            'effective_date_in' => $msc_data['effective_date_in'],
            'effective_date_out' => $msc_data['effective_date_out']
        ];
        return $msc_data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:7',
            'description' => 'required|string|max:40',
            'interior_color' => 'required|string|max:15',
            'car_line' => 'required|string|max:6',
            'model_grade' => 'required|string|max:40',
            'body' => 'required|string|max:5',
            'engine' => 'required|string|max:6',
            'transmission' => 'required|string|max:5',
            'effective_date_in' => 'nullable|date_format:d/m/Y',
            'effective_date_out' => 'nullable|date_format:d/m/Y',
            'plant_code' => 'required|alpha_num_dash|max:5'
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'effective_date_in.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'effective_date_out.date_format' => 'The :attribute does not match the format dd/mm/yyyy'
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
        $this->orderEffectiveDateCheck($this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
        ImportHelper::checkUniqueData($this->uniqueData, Msc::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = $this->createRowsInsert($collection, [
            'effective_date_in' => 'd/m/Y',
            'effective_date_out' =>  'd/m/Y'
        ]);
        Msc::query()->insert($rows);
    }

    /**
     * @throws ValidationException
     */
    private function orderEffectiveDateCheck(array &$failures = [])
    {
        foreach ($this->orderEffectiveDate as $row => $dates) {
            if (!ImportHelper::__isAfterDate($dates['effective_date_in'], $dates['effective_date_out'])) {
                ImportHelper::__handleAfterDateError($failures, $row, $dates, 'effective_date_out', 'Effective Date Out',
                    'Effective Date Out must come after Effective Date In');
            }
        }
    }
}
