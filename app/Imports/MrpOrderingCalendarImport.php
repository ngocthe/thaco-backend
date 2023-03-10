<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\MrpOrderCalendar;
use App\Models\MrpWeekDefinition;
use App\Models\PartGroup;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class MrpOrderingCalendarImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        "contract_no",
        "part_group",
        "mrpor_run_at",
        "supplier_order_span_from",
        "supplier_order_span_to",
        "etd",
        "eta",
        "target_plan_from",
        "target_plan_to",
        "buffer_span_from",
        "buffer_span_to"
    ];

    public const MAP_HEADING_ROW = [
        'contract_code' => 'Contract No.',
        'part_group' => 'Part Group',
        'mrp_or_run' => 'MRP/OR Run At',
        'order_span_from' => 'Supplier Order Span From',
        'order_span_to' => 'Supplier Order Span To',
        'etd' => 'ETD',
        'eta' => 'ETA',
        'target_plan_from' => 'Target Plan From',
        'target_plan_to' => 'Target Plan To',
        'buffer_span_from' => 'Buffer Span From',
        'buffer_span_to' => 'Buffer Span To',

    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['contract_code', 'part_group'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Contract No., Part Group';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The codes: Contract No., Part Group have already been taken.';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var array
     */
    protected array $partGroups = [];

    /**
     * @var array
     */
    protected array $weekMonthYear = [];

    /**
     * @var array
     */
    protected array $excelDateValidateAfterFields = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);
        $orderCalendarData = [
            'contract_code' => strtoupper($data['contract_no']),
            'part_group' => strtoupper($data['part_group']),
            'etd' => $this->excelToDate($data['etd']),
            'eta' => $this->excelToDate($data['eta']),
            'mrp_or_run' => $this->excelToDate($data['mrpor_run_at']),
            'target_plan_from' => strtoupper($data['target_plan_from']),
            'target_plan_to' => strtoupper($data['target_plan_to']),
            'buffer_span_from' => $data['buffer_span_from'] ? strtoupper($data['buffer_span_from']) : null,
            'buffer_span_to' => $data['buffer_span_to'] ? strtoupper($data['buffer_span_to']) : null,
            'order_span_from' => $data['supplier_order_span_from'] ? strtoupper($data['supplier_order_span_from']) : null,
            'order_span_to' => $data['supplier_order_span_to'] ? strtoupper($data['supplier_order_span_to']) : null
        ];

        $this->uniqueData[$index] = [
            'contract_code' => $orderCalendarData['contract_code'],
            'part_group' => $orderCalendarData['part_group']
        ];
        $this->excelDateValidateAfterFields[$index] = [
            'part_group' => $orderCalendarData['part_group'],
            'mrp_or_run' => $orderCalendarData['mrp_or_run'],
            'etd' => $orderCalendarData['etd'],
            'eta' => $orderCalendarData['eta'],
            'target_plan_from' => $orderCalendarData['target_plan_from'],
            'target_plan_to' => $orderCalendarData['target_plan_to'],
            'buffer_span_from' => $orderCalendarData['buffer_span_from'],
            'buffer_span_to' => $orderCalendarData['buffer_span_to'],
            'order_span_from' => $orderCalendarData['order_span_from'],
            'order_span_to' => $orderCalendarData['order_span_to']
        ];
        $this->partGroups[$index] = $orderCalendarData['part_group'];
        $keys = ['target_plan_from', 'target_plan_to', 'buffer_span_from', 'buffer_span_to', 'order_span_from', 'order_span_to'];
        foreach ($keys as $key) {
            if ($orderCalendarData[$key]) {
                $this->weekMonthYear[] = $orderCalendarData[$key];
            }
        }

        return $orderCalendarData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'contract_code' => 'bail|required|alpha_num_dash|max:9',
            'part_group' => 'bail|required|alpha_num_dash|max:2',
            'etd' => 'bail|required|date_format:d/m/Y',
            'eta' => 'bail|required|date_format:d/m/Y',
            'target_plan_from' => 'bail|required|regex:/^W{1,1}\d{1,1}-\d{2,2}\/\d{4,4}$/',
            'target_plan_to' => 'bail|required|regex:/^W{1,1}\d{1,1}-\d{2,2}\/\d{4,4}$/',
            'buffer_span_from' => 'nullable|regex:/^W{1,1}\d{1,1}-\d{2,2}\/\d{4,4}$/',
            'buffer_span_to' => 'nullable|regex:/^W{1,1}\d{1,1}-\d{2,2}\/\d{4,4}$/',
            'order_span_from' => 'nullable|regex:/^W{1,1}\d{1,1}-\d{2,2}\/\d{4,4}$/',
            'order_span_to' => 'nullable|regex:/^W{1,1}\d{1,1}-\d{2,2}\/\d{4,4}$/',
            'mrp_or_run' => 'bail|required|date_format:d/m/Y',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'etd.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'mrp_or_run.date_format' => 'The :attribute does not match the format dd/mm/yyyy',
            'eta.date_format' => 'The :attribute does not match the format dd/mm/yyyy'
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
        ImportHelper::checkUniqueData($this->uniqueData, MrpOrderCalendar::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        $partGroupData = $this->validatePartGroup();
        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $this->validationDatePairs($partGroupData, $this->failures);
        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
        $rows = $this->createRowsInsert($collection, [
            'etd' => 'd/m/Y',
            'eta' => 'd/m/Y',
            'mrp_or_run' => 'd/m/Y'
        ]);
        MrpOrderCalendar::query()->insert($rows);
    }

    /**
     * @return array
     * @throws ValidationException
     */
    private function validatePartGroup(): array
    {
        $partGroups = array_unique($this->partGroups);
        $rows = PartGroup::query()
            ->select('code', 'lead_time', 'ordering_cycle', 'delivery_lead_time')
            ->whereIn('code', $partGroups)
            ->get()
            ->toArray();
        $partGroupData = [];
        foreach ($rows as $row) {
            $partGroupData[$row['code']] = $row;
        }
        foreach ($this->partGroups as $index => $partGroup) {
            if (!isset($partGroupData[$partGroup])) {
                ImportHelper::processErrors($index, 'Part Group', 'The Part Group Code does not exist.', [$partGroup], $this->failures);
            }
        }
        return $partGroupData;
    }

    /**
     * @param $partGroupData
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    private function validationDatePairs($partGroupData, array &$failures = [])
    {
        $this->weekMonthYear = array_unique($this->weekMonthYear);
        $dates = $this->getMrpWeekDefinitionDates($this->weekMonthYear);
        foreach ($this->excelDateValidateAfterFields as $row => $item) {
            $this->validateRequiredWith($row, $item, $failures);
            $mrpRunAt = Carbon::createFromFormat('d/m/Y', $item['mrp_or_run'])->toDateString();
            $etd = Carbon::createFromFormat('d/m/Y', $item['etd'])->toDateString();
            $eta = Carbon::createFromFormat('d/m/Y', $item['eta'])->toDateString();
            $targetPlanFrom = $dates[$item['target_plan_from']][0];
            $targetPlanTo = $dates[$item['target_plan_to']][array_key_last($dates[$item['target_plan_to']])];
            $bufferSpanFrom = $dates[$item['buffer_span_from']][0] ?? null;
            $bufferSpanTo = isset($dates[$item['buffer_span_to']]) ? $dates[$item['buffer_span_to']][array_key_last($dates[$item['buffer_span_to']])] : null;
            $orderSpanFrom = $dates[$item['order_span_from']][0] ?? null;
            $orderSpanTo = isset($dates[$item['order_span_to']]) ? $dates[$item['order_span_to']][array_key_last($dates[$item['order_span_to']])] : null;
            $format = 'Y-m-d';
            if (!ImportHelper::__isAfterDate($mrpRunAt, $orderSpanFrom, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'order_span_from', 'Supplier Order Span From',
                    'Supplier Order Span From must come after MRP/OR Run At');
            }
            if (!ImportHelper::__isAfterDate($orderSpanFrom, $orderSpanTo, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'order_span_to', 'Supplier Order Span To',
                    'Supplier Order Span To must come after Supplier Order Span From');
            }
            if (!ImportHelper::__isAfterDate($orderSpanTo, $etd, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'etd', 'ETD',
                    'ETD must come after Order Span From');
            }
            if (!ImportHelper::__isAfterDate($mrpRunAt, $etd, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'etd', 'ETD',
                    'ETD must come after MRP/OR Run At');
            }
            if (!ImportHelper::__isGreaterThanOrEqualDate($etd, $eta, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'eta', 'ETA',
                    'ETA must come after ETD');
            }
            if (!ImportHelper::__isAfterDate(Carbon::today()->toDateString(), $eta, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'eta', 'ETA',
                    'ETA must come after today');
            }
            if (!ImportHelper::__isAfterDate($eta, $targetPlanFrom, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'target_plan_from', 'Target Plan From',
                    'Target Plan From must come after ETA');
            }
            if (!ImportHelper::__isAfterDate($targetPlanFrom, $targetPlanTo, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'target_plan_to', 'Target Plan To',
                    'Target Plan To must come after Target Plan From ');
            }
            if (!ImportHelper::__isAfterDate($targetPlanTo, $bufferSpanFrom, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'buffer_span_from', 'Buffer Span From',
                    'Buffer Span From must come after Target Plan To');
            } else {
                $diff = date_diff(date_create($bufferSpanFrom), date_create($targetPlanTo));
                if ($diff->days != 1) {
                    ImportHelper::__handleAfterDateError($failures, $row, $item, 'buffer_span_from', 'Buffer Span From',
                        'Field Buffer Span From must be in the week right after Target Plan To');
                }
            }
            if (!ImportHelper::__isAfterDate($bufferSpanFrom, $bufferSpanTo, $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'buffer_span_to', 'Buffer Span To',
                    'Buffer Span To must come after Buffer Span From ');
            }
            $partGroup = $partGroupData[$item['part_group']];
            if ($partGroup['delivery_lead_time']) {
                $orderLeadTime = Carbon::createFromFormat('d/m/Y', $item['mrp_or_run'])->addDays($partGroup['delivery_lead_time'])->toDateString();
            } else {
                $orderLeadTime = Carbon::createFromFormat('d/m/Y', $item['mrp_or_run'])->addWeeks($partGroup['lead_time'])->toDateString();
            }
            $targetPlanFrom = $dates[$item['target_plan_from']][array_key_last($dates[$item['target_plan_from']])];
            if ($targetPlanFrom <= $orderLeadTime) {
                ImportHelper::__handleAfterDateError($failures, $row, $item, 'target_plan_from', 'Target Plan From',
                    'Target Plan From cannot be set within the lead time');
            }
        }
    }

    /**
     * @param $row
     * @param $item
     * @param $failures
     * @return void
     * @throws ValidationException
     */
    private function validateRequiredWith($row, $item, &$failures)
    {
        if ($item['buffer_span_from'] && !$item['buffer_span_to']) {
            ImportHelper::__handleAfterDateError($failures, $row, $item, 'buffer_span_to', 'Buffer Span To',
                'The Buffer Span To field is required when Buffer Span From is present.');
        } elseif (!$item['buffer_span_from'] && $item['buffer_span_to']) {
            ImportHelper::__handleAfterDateError($failures, $row, $item, 'buffer_span_from', 'Buffer Span From',
                'The Buffer Span From field is required when Buffer Span To is present.');
        }

        if ($item['order_span_from'] && !$item['order_span_to']) {
            ImportHelper::__handleAfterDateError($failures, $row, $item, 'order_span_to', 'Supplier Order Span To',
                'Supplier Order Span To field is required when Supplier Order Span From is present.');
        } elseif (!$item['order_span_from'] && $item['order_span_to']) {
            ImportHelper::__handleAfterDateError($failures, $row, $item, 'order_span_from', 'Supplier Order Span From',
                'Supplier Order Span From field is required when Supplier Order Span To is present.');
        }
    }

    /**
     * @param $weekMonthYear
     * @return array
     */
    private function getMrpWeekDefinitionDates($weekMonthYear): array
    {
        $data = [];
        foreach ($weekMonthYear as $item) {
            if (preg_match("/^W\d{1,1}-\d{2,2}\/\d{4,4}$/", $item)) {
                $data[] = $this->getYearMontWeekNo($item);
            }
        }
        $rows = MrpWeekDefinition::whereInMultiple(['year', 'month_no', 'week_no'], $data)
            ->select('year', 'month_no', 'week_no', 'date')
            ->orderBy('date')
            ->get()
            ->toArray();
        $dates = [];
        foreach ($rows as $row) {
            $key = $this->convertRowDataToKey($row);
            $dates[$key][] = $row['date'];
        }
        return $dates;
    }

    /**
     * @param $dateString
     * @return array
     */
    private function getYearMontWeekNo($dateString): array
    {
        $dateString = explode('-', $dateString); // ex W5-08/2022
        $weekNo = str_replace('W', '', $dateString[0]); // ex: W5
        $month = explode('/', $dateString[1]); // [08, 2022]
        $monthNo = $month[0];
        $year = $month[1];
        return [$year, $monthNo, $weekNo];
    }

    /**
     * @param $row
     * @return string
     */
    private function convertRowDataToKey($row): string
    {
        return 'W' . $row['week_no'] . '-' . ($row['month_no'] < 10 ? '0' : '') . $row['month_no'] . '/' . $row['year'];
    }
}
