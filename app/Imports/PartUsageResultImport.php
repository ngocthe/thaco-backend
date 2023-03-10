<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Bom;
use App\Models\Msc;
use App\Models\PartColor;
use App\Models\PartUsageResult;
use App\Models\VehicleColor;
use App\Models\WarehouseInventorySummary;
use App\Services\MrpWeekDefinitionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;

class PartUsageResultImport implements ToCollection, WithStartRow, WithMultipleSheets
{
    public const MAP_HEADING_ROW = [
        'msc_code' => 'MSC',
        'vehicle_color_code' => 'Ext.Color',
        'plant_code' => 'Plant Code'
    ];

    const START_ROW = 6;
    const NUMBER_ROW_HEADING = 8;
    const START_COLUMN = 5;

    /**
     * @var array
     */
    public array $failures = [];

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
    protected array $lineByMsc = [];

    /**
     * @var array
     */
    protected array $lineByPart = [];

    /**
     * @var int|null
     */
    private ?int $numberIndexOfCurrentDay;

    /**
     * @var array
     */
    private array $plantCodes = [];

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
        $collection = $collection->toArray();
        if (!count($collection)) {
            ImportHelper::processErrors(0, 'heading',
                'The heading row invalid',
                [''], $this->failures);
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
        $this->validateData($collection);
        if (!count($this->mscCode)) {
            $this->failures[] = new Failure(self::NUMBER_ROW_HEADING + 1, '', ['The import file has missing data.'], ['']);
        }
        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
        $boms = $this->getPartAndPartColorInBomsByMsc();
        $this->validateMscEffectiveDates();
        list($partCodes, $partQuantities, $rowsInsertOrUpdate) = $this->prepareDataBeforeInsert($boms);
        if (count($partCodes)) {
            $rowsInsertSummary = $this->validateWarehouseInventorySummaryQuantity($partCodes, $partQuantities);
            DB::beginTransaction();
            try {
                if (count($rowsInsertOrUpdate)) {
                    $rowsInsertOrUpdate = array_chunk($rowsInsertOrUpdate, 1000);
                    foreach ($rowsInsertOrUpdate as $data) {
                        PartUsageResult::query()->upsert(
                            $data,
                            ['used_date', 'part_code', 'part_color_code', 'plant_code'],
                            ['quantity' => DB::raw('quantity + VALUES(quantity)'), 'created_by', 'updated_by', 'deleted_at']
                        );
                    }

                    $rowsInsertSummary = array_chunk($rowsInsertSummary, 1000);
                    foreach ($rowsInsertSummary as $rows) {
                        WarehouseInventorySummary::query()->upsert(
                            $rows,
                            ['part_code', 'part_color_code', 'warehouse_type', 'warehouse_code', 'plant_code'],
                            ['quantity']
                        );
                    }
                }

                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
                Log::error($exception);
            }
        }
    }

    /**
     * @param $collection
     * @throws ValidationException
     */
    protected function validateData($collection)
    {
        $todayStr = Carbon::today()->toDateString();
        $numberIndexOfCurrentMonth = $this->getIndexOfMonthNo($collection[0], $todayStr);
        if (!$numberIndexOfCurrentMonth) {
            $this->failures[] = new Failure(self::START_ROW, '', ['Data not found for the current month'], ['']);
        } else {
            $this->validateHeadingRow($collection[2]);
            $this->numberIndexOfCurrentDay = $this->getIndexOfCurrentDay($numberIndexOfCurrentMonth, $todayStr, $collection[2]);
            if (!$this->numberIndexOfCurrentDay) {
                $this->failures[] = new Failure(
                    self::NUMBER_ROW_HEADING,
                    '',
                    ['Data not found for the current day'],
                    ['']
                );
            } else {
                $this->validateFormatData($collection);
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
        }
    }

    /**
     * @param $dayRaw
     * @return void
     * @throws ValidationException
     */
    private function validateHeadingRow($dayRaw)
    {
        foreach ($dayRaw as $index => $day) {
            if ($index < self::START_COLUMN) {
                $titles = ['No.', 'MSC', 'Ext.Color', 'Description', 'Plant Code'];
                if (!in_array($day, $titles)) {
                    ImportHelper::processErrors(self::NUMBER_ROW_HEADING, 'heading',
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
     * @param $collection
     * @return void
     * @throws ValidationException
     */
    private function validateFormatData($collection): void
    {
        foreach ($collection as $index => $row) {
            if ($index > 2) {
                $line = self::START_ROW + $index;
                $row = array_map('trim', $row);
                if ($row[1] || $row[2]) {
                    $partUsageResultData = [
                        'msc_code' => strtoupper($row[1]),
                        'vehicle_color_code' => strtoupper($row[2]),
                        'quantity' => $row[$this->numberIndexOfCurrentDay],
                        'plant_code' => strtoupper($row[4])
                    ];

                    $validator = Validator::make($partUsageResultData, $this->rules(), $this->messages());
                    if ($validator->fails()) {
                        $fails = $validator->failed();
                        $errors = $validator->errors();
                        foreach ($fails as $attribute => $fail) {
                            $messages = $errors->get($attribute);
                            foreach ($messages as $message) {
                                ImportHelper::processErrors(
                                    $line,
                                    self::MAP_HEADING_ROW[$attribute] ?? $attribute,
                                    $message,
                                    [$attribute => $partUsageResultData[$attribute]],
                                    $this->failures
                                );
                            }
                        }
                    } else {
                        $mscCode = $partUsageResultData['msc_code'];
                        $vehicleColorCode = $partUsageResultData['vehicle_color_code'];
                        $plantCode = $partUsageResultData['plant_code'];
                        $this->mscCode[$line] = [$mscCode, $plantCode];
                        $this->vehicleColorCode[$line] = [$vehicleColorCode, $plantCode];
                        $this->plantCodes[$line] = $plantCode;
                        $this->dataByMsc[$mscCode . '|' . $plantCode][] = [
                            'vehicle_color_code' => $vehicleColorCode,
                            'quantity' => $partUsageResultData['quantity'],
                            'plant_code' => $plantCode
                        ];
                        $this->lineByMsc[$mscCode . '|' . $vehicleColorCode . '|' . $plantCode] = $line;
                        $this->uniqueData[] = [
                            $mscCode,
                            $vehicleColorCode,
                            $plantCode
                        ];
                    }
                }
            }
        }
    }

    /**
     * @return string[]
     */
    private function rules(): array
    {
        return [
            'msc_code' => 'required|alpha_num_dash|max:7',
            'vehicle_color_code' => 'required|alpha_num_dash|max:4',
            'quantity' => 'required|integer|min:0|max:9999',
        ];
    }

    /**
     * @return array
     */
    private function messages(): array
    {
        return [
            'msc_code.required' => 'The MSC field is required.',
            'msc_code.alpha_num_dash' => 'The MSC must only contain upper letters numbers and dash, start and end with a letter',
            'msc_code.max' => 'The MSC must not be greater than 7 characters.',
            'vehicle_color_code.required' => 'The Ext. Color field is required.',
            'vehicle_color_code.alpha_num_dash' => 'The Ext. Color must only contain upper letters numbers and dash, start and end with a letter',
            'vehicle_color_code.max' => 'The Ext. Color must not be greater than 4 characters',
            'quantity.required' => 'The quantity field is required.',
            'quantity.integer' => 'The quantity must be a positive integer.',
            'quantity.min' => 'The quantity must be a positive integer.',
            'quantity.max' => 'The quantity must not be greater than 9999.',
        ];
    }


    /**
     * @param $rowMonthNo
     * @param $dateString
     * @return int|string
     */
    private function getIndexOfMonthNo($rowMonthNo, $dateString)
    {
        $today = Carbon::parse($dateString);
        $currentMonthString = [
            $today->format('m/Y'),
            $today->format('n/Y')
        ];

        $numberIndexOfCurrentMonth = null;
        foreach ($rowMonthNo as $index => $monthNo) {
            if (in_array(trim($monthNo), $currentMonthString)) {
                $numberIndexOfCurrentMonth = $index;
                break;
            }
        }
        return $numberIndexOfCurrentMonth;
    }

    /**
     * @param $numberIndexOfCurrentMonth
     * @param $dateString
     * @param $rowDays
     * @return int|null
     */
    private function getIndexOfCurrentDay($numberIndexOfCurrentMonth, $dateString, $rowDays): ?int
    {
        $currentDate = Carbon::parse($dateString);
        $firstOfMonth = Carbon::parse($dateString)->firstOfMonth();
        list($monthNo) = (new MrpWeekDefinitionService())->getMonthWeekNoOfDate($firstOfMonth->toDateString());

        if ($monthNo == $firstOfMonth->month) {
            $diff = $currentDate->day - $firstOfMonth->dayOfWeek + 2;
        } else {
            $diff = $currentDate->day - (8 - $firstOfMonth->dayOfWeekIso) - 3;
        }
        $index = $numberIndexOfCurrentMonth + $diff;
        if ($rowDays[$index] != $currentDate->day) {
            return null;
        } else {
            return $index;
        }
    }

    /**
     * @return Builder[]|Collection
     */
    private function getPartAndPartColorInBomsByMsc()
    {
        return Bom::whereInMultiple(['msc_code', 'plant_code'], $this->mscCode)
            ->with('ecnInInfo', 'ecnOutInfo')
            ->selectRaw('msc_code, part_code, part_color_code, ecn_in, ecn_out, plant_code, SUM(quantity) as quantity')
            ->groupBy(['msc_code', 'part_code', 'part_color_code', 'ecn_in', 'ecn_out', 'plant_code'])
            ->get();
    }

    /**
     * @param $boms
     * @return array
     */
    private function prepareDataBeforeInsert($boms): array
    {
        $loggedId = auth()->id();
        $currentDate = Carbon::now()->toDateString();
        $partCodes = [];
        $partQuantities = [];
        $rowsInsertOrUpdate = [];
        foreach ($boms as $bom) {
            if (!$this->isValidEcnInOutDate($bom, $currentDate)) {
                continue;
            }
            $rowsImport = $this->dataByMsc[$bom['msc_code'] . '|' . $bom['plant_code']];
            foreach ($rowsImport as $rowImport) {
                $quantity = $bom['quantity'] * $rowImport['quantity'];
                $partColorCode = $this->getPartColorCode($bom['part_color_code'], $bom['part_code'], $rowImport['vehicle_color_code']);
                $partCode = [$bom['part_code'], $partColorCode, $bom['plant_code']];
                $key = implode('-', $partCode);
                if (!isset($partQuantities[$key])) {
                    $partQuantities[$key] = 0;
                    $rowsInsertOrUpdate[$key] = [
                        'used_date' => $currentDate,
                        'part_code' => $bom['part_code'],
                        'part_color_code' => $partColorCode,
                        'plant_code' => $bom['plant_code'],
                        'quantity' => 0,
                        'created_by' => $loggedId,
                        'updated_by' => $loggedId,
                        'deleted_at' => null
                    ];
                }
                $partQuantities[$key] += $quantity;
                $rowsInsertOrUpdate[$key]['quantity'] += $quantity;
                $partCodes[$key] = $partCode;
                $line = $this->lineByMsc[$bom['msc_code'] . '|' . $rowImport['vehicle_color_code'] . '|' . $bom['plant_code']];
                $this->lineByPart[$key] = $line;
            }
        }
        return [$partCodes, $partQuantities, $rowsInsertOrUpdate];
    }

    /**
     * @param Bom $bom
     * @param $today
     * @return false
     */
    private function isValidEcnInOutDate(Bom $bom, $today): bool
    {
        $ecnInDate = $bom['ecnInInfo']['actual_line_off_date'] ?? null;
        $ecnOutDate = $bom['ecnOutInfo']['actual_line_off_date'] ?? null;
        if ((!$ecnInDate || $ecnInDate->toDateString() <= $today) && (!$ecnOutDate || $ecnOutDate->toDateString() >= $today)) {
            return true;
        }
        return false;
    }

    /**
     * @param $partCodes
     * @param $partQuantities
     * @return array
     * @throws ValidationException
     */
    private function validateWarehouseInventorySummaryQuantity($partCodes, $partQuantities): array
    {
        $whSummaries = WarehouseInventorySummary::whereInMultiple(
            ['part_code', 'part_color_code', 'plant_code'],
            $partCodes
        )
            ->select('part_code', 'part_color_code', 'plant_code', 'quantity', 'warehouse_code', 'warehouse_type',
                'created_by', 'updated_by')
            ->where('warehouse_type', WarehouseInventorySummary::TYPE_PLANT_WH)
            ->get()
            ->toArray();

        $rowsInsertSummary = [];
        if (count($whSummaries) != count($partCodes)) {
            $partCodesSummaries = array_map(function ($wh) {
                return implode('-', [$wh['part_code'], $wh['part_color_code'], $wh['plant_code']]);
            }, $whSummaries);

            foreach ($partCodes as $key => $data) {
                if(!in_array($key, $partCodesSummaries)) {
                    $this->failures[] = new Failure(
                        $this->lineByPart[$key],
                        '',
                        ['The number of part usage of Part: ' . $data[0] . ' and Part color: ' . $data[1] . ' must not be greater than current summary'],
                        [$partQuantities[$key]]
                    );
                }
            }
        } else {
            foreach ($whSummaries as $summary) {
                $partCode = [$summary['part_code'], $summary['part_color_code'], $summary['plant_code']];
                $key = implode('-', $partCode);
                if ($partQuantities[$key] > $summary['quantity']) {
                    $this->failures[] = new Failure(
                        $this->lineByPart[$key],
                        '',
                        ['The number of part usage of Part: ' . $summary['part_code'] . ' and Part color: ' . $summary['part_color_code'] . ' must not be greater than current summary: ' . $summary['quantity']],
                        [$partQuantities[$key]]
                    );
                } else {
                    $summary['quantity'] -= $partQuantities[$key];
                    $rowsInsertSummary[] = $summary;
                }
            }
        }

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
        return $rowsInsertSummary;
    }

    /**
     * @param $partColorCode
     * @param $partCode
     * @param $vehicleColorCode
     * @return HigherOrderBuilderProxy|mixed|string
     */
    private function getPartColorCode($partColorCode, $partCode, $vehicleColorCode)
    {
        if ($partColorCode == 'XX') {
            $partColor = PartColor::query()
                ->select('code')
                ->where([
                    'part_code' => $partCode,
                    'vehicle_color_code' => $vehicleColorCode
                ])->first();
            return $partColor ? $partColor->code : 'XX';
        } else {
            return $partColorCode;
        }
    }

    /**
     * @return void
     * @throws ValidationException
     */
    private function validateMscEffectiveDates()
    {
        $today = Carbon::now()->toDateString();
        $mscs = Msc::whereInMultiple(['code', 'plant_code'], $this->mscCode)
            ->select('code', 'plant_code', 'effective_date_in', 'effective_date_out')
            ->get();
        foreach ($mscs as $msc) {
            $effectiveDateIn = $msc->effective_date_in ? $msc->effective_date_in->toDateString() : null;
            $effectiveDateOut = $msc->effective_date_out ? $msc->effective_date_out->toDateString() : null;
            if ((!$effectiveDateIn || ($effectiveDateIn <= $today)) && (!$effectiveDateOut || ($effectiveDateOut >= $today))) {
                // code somthing here
            } else {
                ImportHelper::processErrors(
                    array_search([$msc->code, $msc->plant_code], $this->mscCode),
                    'MSC',
                    'MSC has passed its effective date',
                    [$msc['code']],
                    $this->failures);
            }
        }
        if (count($this->failures))
        {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }
    }
}
