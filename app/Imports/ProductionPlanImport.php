<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\MrpWeekDefinition;
use App\Models\Msc;
use App\Models\ProductionPlan;
use App\Models\Setting;
use App\Services\ProductionPlanService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductionPlanImport implements ToCollection, WithStartRow, WithMultipleSheets
{
    public const MAP_HEADING_ROW = [
        'msc_code' => 'MSC',
        'vehicle_color_code' => 'Ext.Color',
        'plant_code' => 'Plant Code'
    ];

    const START_ROW = 7;
    const START_COLUMN = 5;

    /**
     * @var array
     */
    public array $failures = [];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = [
        'msc_code',
        'vehicle_color_code'
    ];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'MSC, Ext.Color, Plant Code';

    /**
     * @var array
     */
    protected array $mscCode = [];

    /**
     * @var array
     */
    protected array $vehicleColorCode = [];

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var array
     */
    protected array $dataByMsc = [];

    /**
     * @var array
     */
    protected array $totalVolumeByDate = [];

    /**
     * @var array
     */
    private array $planDate = [];

    /**
     * @var array
     */
    private array $mscRows = [];

    /**
     * @var array
     */
    private array $mscEffectiveDates = [];

    /**
     * @var array
     */
    private array $plantCodes = [];

    /**
     * @var ProductionPlanService
     */
    public ProductionPlanService $productionPlanService;

    /**
     * @var int
     */
    private int $importId;

    public function __construct($importId)
    {
        $this->importId = $importId;
        $this->productionPlanService = new ProductionPlanService();
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return self::START_ROW;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    /**
     * @param Collection $collection
     * @return void
     * @throws ValidationException
     */
    public function collection(Collection $collection)
    {
        if (!count($collection)) {
            ImportHelper::processErrors(0, 'heading',
                'The heading row invalid',
                [''], $this->failures);
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $collection = $collection->toArray();
        $this->validateData($collection);

        if (!count($this->dataByMsc)) {
            ImportHelper::processErrors(
                10,
                '',
                'The import file has missing data.',
                [null],
                $this->failures);
        }
        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
        $this->getMscEffectiveDates();
        $dataInsert = [];
        $loggedId = auth()->id();
        $currentDate = Carbon::now()->toDateTimeString();
        foreach ($this->dataByMsc as $key => $rows) {
            $keys = explode('|', $key);
            $mscCode = $keys[0];
            $mscRowsInsert = [];
            foreach ($rows as $row) {
                $plantCode = $row['plant_code'];
                $msc = $this->mscEffectiveDates[$mscCode . '|' . $plantCode];
                $effectiveDateIn = $msc[0];
                $effectiveDateOut = $msc[1];
                $planDate = $row['plan_date'];
                if ((!$effectiveDateIn || ($effectiveDateIn <= $planDate)) && (!$effectiveDateOut || ($effectiveDateOut >= $planDate))) {
                    $mscRowsInsert[] = array_merge($row, [
                        'import_id' => $this->importId,
                        'created_by' => $loggedId,
                        'updated_by' => $loggedId,
                        'created_at' => $currentDate,
                        'updated_at' => $currentDate
                    ]);
                } else {
                    ImportHelper::processErrors(
                        $this->mscRows[$key],
                        'plan date',
                        'There is production plan date not between MSC effective date in and out',
                        [null],
                        $this->failures);
                    throw ValidationException::withMessages(['failures' => $this->failures]);
                }
            }
            if (count($mscRowsInsert)) {
                $dataInsert = array_merge($dataInsert, $mscRowsInsert);
            } else {
                ImportHelper::processErrors(
                    $this->mscRows[$key],
                    'plan date',
                    'There is production plan date not between MSC effective date in and out',
                    [null],
                    $this->failures);
                throw ValidationException::withMessages(['failures' => $this->failures]);
            }
        }

        DB::beginTransaction();
        try {
            $dataInsert = array_chunk($dataInsert, 1000);
            foreach ($dataInsert as $data) {
                ProductionPlan::query()->insert($data);
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }
    }

    /**
     * @param $collection
     * @throws ValidationException
     */
    protected function validateData($collection)
    {
        $this->validateHeadingRow($collection[0], $collection[2]);
        $this->validateFormatData($collection);
        $this->validateMaxVolumeAndWorkingDay();
        $duplicate = ImportHelper::findDuplicateInMultidimensional($this->uniqueData);
        if (count($duplicate)) {
            ImportHelper::handleDuplicateError($duplicate, $this->uniqueAttributes, $this->failures);
        }
        ImportHelper::referenceCheckDataPair($this->mscCode, ['code', 'plant_code'], Msc::class,
            'MSC, Plant Code',
            'MSC, Plant Code are not linked together.',
            $this->failures);
        ImportHelper::referenceCheckVehicleColorCode($this->vehicleColorCode, 'EXT', 'Ext.Color',
            'vehicle_color_code', $this->failures);
        ImportHelper::referenceCheckPlantCode($this->plantCodes, $this->failures);
    }


    /**
     * @param $monthRaw
     * @param $dayRaw
     * @return void
     * @throws ValidationException
     */
    private function validateHeadingRow($monthRaw, $dayRaw)
    {
        $this->validateMrpHeadingRow($dayRaw);
        list($days, $months) = $this->getDaysAndMonthsValid($monthRaw);
        $this->validateDaysAndMonths($months, $days, $dayRaw);
        $daysValue = array_keys($days);
        $this->planDate = $days;

        foreach ($dayRaw as $index => $day) {
            if ($index >= self::START_COLUMN) {
                $dayIdx = $index - self::START_COLUMN;
                $dayStr = $daysValue[$dayIdx]; // y-m-d
                if ($day != explode('-', $dayStr)[2]) {
                    list($monthYear, $weekNo) = $this->getWeekMonthYear($dayStr);
                    ImportHelper::processErrors(9, 'day',
                        'Day ' . $day . ' does not match the information of ' . $monthYear . ', ' . $weekNo,
                        [$day], $this->failures);
                }
            }
        }
        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
    }

    /**
     * @param $value
     * @return bool
     */
    private function isValidMonthYear($value): bool
    {
        return preg_match("/^([1-9]|0[1-9]|1[0-2])\/([0-9]{4})$/", $value);
    }

    /**
     * @param $dayRaw
     * @return void
     * @throws ValidationException
     */
    private function validateMrpHeadingRow($dayRaw)
    {
        foreach ($dayRaw as $index => $day) {
            if ($index < self::START_COLUMN) {
                $titles = ['No.', 'MSC', 'Ext.Color', 'Description', 'Plant Code'];
                if (!in_array($day, $titles)) {
                    ImportHelper::processErrors(9, 'heading',
                        'The heading row invalid',
                        [$day], $this->failures);
                    throw ValidationException::withMessages(['failures' => $this->failures]);
                }
            } else {
                break;
            }
        }
    }

    /**
     * @param $monthRaw
     * @return array
     * @throws ValidationException
     */
    private function getDaysAndMonthsValid($monthRaw): array
    {
        $days = [];
        $months = [];
        try {
            foreach ($monthRaw as $index => $month) {
                if ($index >= self::START_COLUMN) {
                    if ($index == 7 && !$month) {
                        ImportHelper::processErrors(7, 'month and year', 'The N (MRP Run Month) is required', [$month], $this->failures);
                    } elseif ($month) {
                        if ($this->isValidMonthYear($month)) {
                            $monthYear = explode('/', $month);
                            $days = array_merge($days, $this->getDaysInMonthYear($monthYear));
                            $months[] = $month;
                        } else {
                            ImportHelper::processErrors(7, 'month and year', 'The month and year values are incorrect', [$month], $this->failures);
                        }
                    }
                }
            }
        } catch (Exception $exception) {
            ImportHelper::processErrors(7, 'month and year', 'The month and year values are incorrect', [$month], $this->failures);
            Log::error($exception);
        }
        return [$days, $months];
    }

    /**
     * @param $months
     * @param $days
     * @param $dayRaw
     * @return void
     * @throws ValidationException
     * - N là tháng hiện tại
     * - Giá trị cột thứ 7 phải là 1 tháng hợp lệ
     * - Các tháng phải là liên tiếp nhau
     * - Bắt đầu từ tháng nhỏ nhất là N -6
     * - phải tồn tại ít nhất các tháng từ N -> N + 6 trong file import
     */
    private function validateDaysAndMonths($months, $days, $dayRaw)
    {
        $firstMonth = explode('/', $months[0]);
        $firstMonthNumber = Carbon::create($firstMonth[1], $firstMonth[0])->floorMonth();
        $startMonth = Carbon::now()->subMonths(6)->floorMonth();

        $today = Carbon::now();
        $monthsValid = [$today->format('m/Y'), $today->format('n/Y')];
        for ($i = 1; $i <= 6; $i++) {
            $nextMonth = $today->addMonth();
            $monthsValid[] = $nextMonth->format('m/Y');
            $monthsValid[] = $nextMonth->format('n/Y');
        }
        if ($startMonth->isAfter($firstMonthNumber)) {
            ImportHelper::processErrors(7, 'month and year', 'The N (MRP Run Month) must be greater than or equal to the month: ' . $startMonth->format('m/Y'), [''], $this->failures);
        } elseif (!$this->isConsecutiveMonths($months, $firstMonthNumber)) {
            ImportHelper::processErrors(7, 'month and year', 'The MRP Months are not consecutive months', [''], $this->failures);
        } elseif (count($months) < 6 || count(array_intersect($months, $monthsValid)) < 7) {
            ImportHelper::processErrors(7, 'month and year', 'The MRP Months minimum from month N to N+6', [''], $this->failures);
        } elseif (!count($days)) {
            ImportHelper::processErrors(7, 'month and year', 'The month and year field is required', [''], $this->failures);
        } elseif (count($days) != count($dayRaw) - self::START_COLUMN) {
            ImportHelper::processErrors(7, 'month and year', 'The month and year data is missing', [''], $this->failures);
        }

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
    }

    /**
     * @param $months
     * @param $flagMonthNumber
     * @return bool
     */
    private function isConsecutiveMonths($months, $flagMonthNumber): bool
    {
        $isConsecutiveMonth = true;
        foreach ($months as $month) {
            $month = explode('/', $month);
            $monthNumber = Carbon::create($month[1], $month[0])->floorMonth();
            if ($flagMonthNumber->isAfter($monthNumber) || $flagMonthNumber->diffInMonths($monthNumber) > 1) {
                $isConsecutiveMonth = false;
                break;
            } else {
                $flagMonthNumber = $monthNumber;
            }
        }
        return $isConsecutiveMonth;
    }

    /**
     * @param $monthYear
     * @return array
     */
    private function getDaysInMonthYear($monthYear): array
    {
        $query = MrpWeekDefinition::query()
            ->select('date', 'month_no', 'week_no', 'day_off')
            ->where('year', $monthYear[1])
            ->where('month_no', $monthYear[0]);
        $rows = $query->orderBy('date')
            ->get()
            ->toArray();
        $data = [];
        foreach ($rows as $row) {
            $data[$row['date']] = $row;
        }
        return $data;
    }

    /**
     * @param $dayStr
     * @return string[]
     */
    private function getWeekMonthYear($dayStr): array
    {
        $row = $this->planDate[$dayStr];
        $monthYear = $row['month_no'] . '/' . explode('-', $row['date'])[0];
        $weekNo = 'MRP Week ' . $row['week_no'];
        return [$monthYear, $weekNo];
    }

    /**
     * @param $collection
     * @return void
     * @throws ValidationException
     */
    private function validateFormatData($collection): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        foreach ($collection as $index => $row) {
            if ($index > 2) {
                $line = self::START_ROW + $index;
                $row = array_map('trim', $row);
                $mscCode = strtoupper($row[1]);
                $vehicleColorCode = strtoupper($row[2]);
                $plantCode = strtoupper($row[4]);

                $rowInvalid = $this->validateFormatMscAndVehicleColor($mscCode, $vehicleColorCode, $line);
                if ($rowInvalid) continue;

                $productionPlantData = $this->prepareProductionPlantData($row, $line, $tomorrow);
                if (count($productionPlantData)) {
                    $rowInvalid = $this->validateVolume($productionPlantData, $line);
                    if ($rowInvalid) continue;

                    $this->mscCode[$line] = [$mscCode, $plantCode];
                    $this->vehicleColorCode[$line] = [$vehicleColorCode, $plantCode];
                    $this->plantCodes[$line] = $plantCode;
                    $this->uniqueData[$line] = [
                        $mscCode,
                        $vehicleColorCode,
                        $plantCode
                    ];
                    $key = $mscCode . '|' . $vehicleColorCode . '|' . $plantCode;
                    $this->dataByMsc[$key] = $productionPlantData;
                    $this->mscRows[$key] = $line;
                }
            }
        }
    }

    /**
     * @param $mscCode
     * @param $vehicleColorCode
     * @param $line
     * @return bool
     * @throws ValidationException
     */
    private function validateFormatMscAndVehicleColor($mscCode, $vehicleColorCode, $line): bool
    {
        $validator = Validator::make(
            [
                'msc_code' => $mscCode,
                'vehicle_color_code' => $vehicleColorCode
            ],
            [
                'msc_code' => 'required|alpha_num_dash|max:7',
                'vehicle_color_code' => 'required|alpha_num_dash|max:4'
            ],
            [
                'msc_code.required' => 'The MSC field is required',
                'msc_code.alpha_num_dash' => 'The MSC must only contain upper letters numbers and dash, start and end with a letter',
                'msc_code.max' => 'The MSC must not be greater than :max characters.',
                'vehicle_color_code.required' => 'The Ext.Color field is required',
                'vehicle_color_code.alpha_num_dash' => 'The Ext.Color must only contain upper letters numbers and dash, start and end with a letter',
                'vehicle_color_code.max' => 'The Ext.Color must not be greater than :max characters.'
            ]
        );

        if ($validator->fails()) {
            $fails = $validator->failed();
            $errors = $validator->errors();
            foreach ($fails as $attribute => $fail) {
                $messages = $errors->get($attribute);
                foreach ($messages as $message) {
                    ImportHelper::processErrors(
                        $line,
                        $attribute,
                        $message,
                        [$attribute == 'msc_code' ? $mscCode : $vehicleColorCode],
                        $this->failures
                    );
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param $rawData
     * @param $line
     * @param $tomorrow
     * @return array
     * @throws ValidationException
     */
    private function prepareProductionPlantData($rawData, $line, $tomorrow): array
    {
        $mscCode = strtoupper($rawData[1]);
        $vehicleColorCode = strtoupper($rawData[2]);
        $productionPlantData = [];
        $daysValue = array_keys($this->planDate);
        foreach ($rawData as $index => $data) {
            if ($index >= self::START_COLUMN && $data) {
                if (!is_numeric($data)) {
                    ImportHelper::processErrors(
                        $line,
                        'volume',
                        'The volume must be a positive integer, at column: ' . $index,
                        [$data],
                        $this->failures
                    );
                } else {
                    $idx = $index - self::START_COLUMN;
                    $plantDate = $daysValue[$idx];
                    if ($plantDate < $tomorrow) continue;
                    $volume = intval($data);
                    $productionPlantData[] = [
                        'plan_date' => $plantDate,
                        'msc_code' => $mscCode,
                        'vehicle_color_code' => $vehicleColorCode,
                        'volume' => $volume,
                        'plant_code' => strtoupper($rawData[4])
                    ];
                    if (!isset($this->totalVolumeByDate[$plantDate])) {
                        $this->totalVolumeByDate[$plantDate] = 0;
                    }
                    $this->totalVolumeByDate[$plantDate] += $volume;
                }

            }
        }
        return $productionPlantData;
    }

    /**
     * @throws ValidationException
     */
    private function validateVolume($productionPlantData, $line): bool
    {
        $dataToValidate = [
            'data' => $productionPlantData
        ];

        $validator = Validator::make($dataToValidate, $this->rules());
        if ($validator->fails()) {
            $fails = $validator->failed();
            $errors = $validator->errors();
            foreach ($fails as $attribute => $fail) {
                $messages = $errors->get($attribute);
                $idx = explode('.', $attribute)[1];
                $dataValidate = $productionPlantData[$idx];
                list($monthYear, $weekNo) = $this->getWeekMonthYear($dataValidate['plan_date']);
                $day = explode('-', $dataValidate['plan_date'])[2];
                foreach ($messages as $message) {
                    $message = str_replace($attribute, 'volume of day:' . $day . ', ' . $monthYear . ', ' . $weekNo, $message);
                    ImportHelper::processErrors($line, 'volume',
                        $message,
                        [$dataValidate['volume']], $this->failures);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @return string[]
     */
    private function rules(): array
    {
        return [
            'data' => 'required|array',
            'data.*.volume' => 'nullable|integer|min:0|max:9999'
        ];
    }

    /**
     * @return void
     * @throws ValidationException
     */
    private function validateMaxVolumeAndWorkingDay()
    {
        $setting = Setting::query()->where('key', 'max_product')->first();
        $maxProduct = $setting ? $setting->value[0] : 1000;
        foreach ($this->totalVolumeByDate as $plantDate => $totalVolume) {
            if ($totalVolume > $maxProduct) {
                $day = explode('-', $plantDate)[2];
                list($monthYear, $weekNo) = $this->getWeekMonthYear($plantDate);
                ImportHelper::processErrors(0, 'volume',
                    'Total volume of the day ' . $day . ', ' . $monthYear . ', ' . $weekNo . ' should not be more than ' . $maxProduct,
                    [$totalVolume], $this->failures);
            } elseif ($this->planDate[$plantDate]['day_off'] && $totalVolume) {
                ImportHelper::processErrors(0, 'day',
                    'Production Plan Date is not working day.',
                    [$plantDate], $this->failures);
            }
        }
    }

    /**
     * @return void
     */
    private function getMscEffectiveDates()
    {
        $mscs = Msc::whereInMultiple(['code', 'plant_code'], $this->mscCode)
            ->select('code', 'plant_code', 'effective_date_in', 'effective_date_out')
            ->get();
        foreach ($mscs as $msc) {
            $key = $msc['code'] . '|' . $msc['plant_code'];
            $this->mscEffectiveDates[$key] = [
                $msc->effective_date_in ? $msc->effective_date_in->toDateString() : null,
                $msc->effective_date_out ? $msc->effective_date_out->toDateString() : null
            ];
        }
    }
}
