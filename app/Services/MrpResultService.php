<?php

namespace App\Services;

use App\Models\MrpProductionPlanImport;
use App\Models\MrpResult;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MrpResultService extends BaseService
{
    /**
     * @var array|null
     */
    public ?array $currentFilterImport = null;

    /**
     * @return string
     */
    public function model(): string
    {
        return MrpResult::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        $this->addFilterImportFile($params, MrpProductionPlanImport::STATUS_RAN_MRP);
        $this->addFilterMrpWeekDefinitionMonthYear($params);

        if (isset($params['msc_code']) && $this->checkParamFilter($params['msc_code'])) {
            $this->whereLike('msc_code', $params['msc_code']);
        }

        if (isset($params['vehicle_color_code']) && $this->checkParamFilter($params['vehicle_color_code'])) {
            $this->whereLike('vehicle_color_code', $params['vehicle_color_code']);
        }

        if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
            $this->query->leftJoin('parts', function ($join) {
                $join->on('mrp_results.part_code', '=', 'parts.code');
            });
            $this->query->where('group', $params['part_group']);
        }

        if (isset($params['plant_code']) && $this->checkParamFilter($params['plant_code'])) {
            $this->query->where('mrp_results.plant_code', $params['plant_code']);
        }

        $this->addFilterPartAndPartColor($params, true);
        $this->currentFilterImport = $params['import_file'] ?? null;
    }

    /**
     * @param bool $isPaginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function getMrpResultsByPart(bool $isPaginate = true)
    {
        DB::statement('SET SESSION group_concat_max_len = 10000');
        $params = request()->toArray();
        $groupBy = request()->get('group_by');
        $limit = (int)($params['per_page'] ?? 20);
        $this->query->selectRaw("
            part_code,
            part_color_code,
            mrp_results.plant_code,
            GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities
        ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date');
        if ($groupBy == 'month') {
            $this->query->selectRaw("GROUP_CONCAT(month_no SEPARATOR ',') as months");
        } elseif ($groupBy == 'week') {
            $this->query->selectRaw("GROUP_CONCAT(week_no SEPARATOR ',') as weeks");
        } else {
            $this->query->selectRaw("GROUP_CONCAT(production_date SEPARATOR ',') as days");
        }
        $this->buildBasicQuery($params);
        $this->query
            ->groupBy(['part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('part_code');
        if ($isPaginate) {
            return $this->query->paginate($limit);
        } else {
            return $this->query->get();
        }
    }

    /**
     * @param bool $isPaginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function getMrpResultsByMSC(bool $isPaginate = true)
    {
        DB::statement('SET SESSION group_concat_max_len = 10000');
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);
        $groupBy = request()->get('group_by', 'day');
        $this->query->selectRaw("
            msc_code,
            vehicle_color_code,
            part_code,
            part_color_code,
            mrp_results.plant_code,
            GROUP_CONCAT(part_requirement_quantity SEPARATOR ',') as quantities
        ")->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date');
        if ($groupBy == 'month') {
            $this->query->selectRaw("GROUP_CONCAT(month_no SEPARATOR ',') as months");
        } elseif ($groupBy == 'week') {
            $this->query->selectRaw("GROUP_CONCAT(week_no SEPARATOR ',') as weeks");
        } else {
            $this->query->selectRaw("GROUP_CONCAT(production_date SEPARATOR ',') as days");
        }
        $this->buildBasicQuery($params);
        $this->query
            ->groupBy(['msc_code', 'vehicle_color_code', 'part_code', 'part_color_code', 'mrp_results.plant_code'])
            ->orderBy('msc_code');
        if ($isPaginate) {
            return $this->query->paginate($limit);
        } else {
            return $this->query->get();
        }
    }

    /**
     * @param $importId
     * @param $mrpRunDate
     * @return array
     */
    public function systemRun($importId, $mrpRunDate): array
    {
        $mrpRunDate = Carbon::createFromFormat('n/d/Y', $mrpRunDate)->toDateString();
        list($status, $message) = ProductionPlanService::validateProductionPlan($importId);
        if (!$status) {
            return [$status, $message];
        }
        $data = [
            'production_plan_id' => $importId,
            'user_code' => auth()->id(),
            'mrp_run_date' => $mrpRunDate
        ];

        $response = Http::baseUrl(config('env.MRP_URL'))
            ->asForm()
            ->post('/mrp/v1/system_run', $data);

        if ($response->status() == 200) {
            return [true, null];
        } else {
            return [false, $response->body()];
        }
    }

    /**
     * @param $mrpResults
     * @param string $groupBy
     * @return array
     */
    public function getProductionPlanVolume($mrpResults, string $groupBy = 'day'): array
    {
        $mscCodes = [];
        foreach ($mrpResults as $mrpResult) {
            $mscCodes[] = $mrpResult->msc_code;
        }
        $mscCodes = array_unique($mscCodes);
        $params = request()->toArray();
        list($selectRaw, $groupByQuery) = $this->getSelectRawAndGroupBy($params);
        $this->defaultRelations = [];
        $this->query = $this->model->query();
        $this->query
            ->selectRaw($selectRaw)
            ->join('mrp_week_definitions', 'mrp_results.production_date', '=', 'mrp_week_definitions.date');
        $this->buildProductionPlanVolumeFilter($params);
        $rawData = $this->query
            ->whereIn('msc_code', $mscCodes)
            ->groupBy($groupByQuery)
            ->orderByRaw("FIELD(msc_code, '".implode("', '" , $mscCodes)."')")
            ->get()
            ->toArray();

        return $this->handleMscResultProductionVolume($rawData, $groupBy, $mscCodes);
    }

    /**
     * @param $rawData
     * @param $groupBy
     * @param $mscCodes
     * @return array
     */
    private function handleMscResultProductionVolume($rawData, $groupBy, $mscCodes): array
    {
        $mscData = [];
        if (count($rawData)) {
            if ($groupBy == 'week') {
                $key = 'week_no';
            } elseif ($groupBy == 'month') {
                $key = 'month_no';
            } else {
                $key = 'production_date';
            }
            foreach ($rawData as $data) {
                $mscCode = $data['msc_code'];
                unset($data['msc_code']);
                if (!isset($mscData[$mscCode][$data[$key]])) {
                    $mscData[$mscCode][$data[$key]] = [
                        $key => $data[$key],
                        'volume' => 0
                    ];
                }
                $mscData[$mscCode][$data[$key]]['volume'] += $data['production_volume'];
            }
            foreach ($mscData as $msc => $data) {
                $mscData[$msc] = array_values($data);
            }
        } elseif (count($mscCodes)) {
            return $this->setDefaultValue($groupBy, $mscCodes);
        }
        return $mscData;
    }

    /**
     * @param $groupBy
     * @param $mscCodes
     * @return array
     */
    private function setDefaultValue($groupBy, $mscCodes): array
    {
        $mscData = [];
        if ($groupBy == 'day') {
            $dates = (new MrpWeekDefinitionService())->getDates(null, true);
            foreach ($mscCodes as $mscCode) {
                foreach ($dates as $date) {
                    $mscData[$mscCode][] = [
                        'volume' => 0,
                        'plan_date' => $date['date']
                    ];
                }
            }
        } elseif ($groupBy == 'week') {
            $weeks = (new MrpWeekDefinitionService())->getWeeks();
            foreach ($mscCodes as $mscCode) {
                foreach ($weeks as $week) {
                    $mscData[$mscCode][] = [
                        'week_no' => $week,
                        'volume' => 0
                    ];
                }
            }
        } elseif ($groupBy == 'month') {
            foreach ($mscCodes as $mscCode) {
                for ($i = 1; $i <= 12; $i++) {
                    $mscData[$mscCode][] = [
                        'month_no' => $i,
                        'volume' => 0
                    ];
                }
            }
        }
        return $mscData;
    }

    /**
     * @param $params
     * @return array
     */
    private function getSelectRawAndGroupBy($params): array
    {
        if (isset($params['group_by']) && $this->checkParamFilter($params['group_by'])) {
            $groupBy = $params['group_by'];
        } else {
            $groupBy = 'day';
        }

        if ($groupBy == 'month') {
            $selectRaw = "msc_code, vehicle_color_code, month_no, production_date, production_volume";
            $groupBy = ['msc_code', 'vehicle_color_code', 'month_no', 'production_date', 'production_volume'];
        } elseif ($groupBy == 'week') {
            $selectRaw = "msc_code, vehicle_color_code, week_no, production_date, production_volume";
            $groupBy = ['msc_code', 'vehicle_color_code', 'week_no', 'production_date', 'production_volume'];
        } else {
            $selectRaw = "msc_code, vehicle_color_code, production_date, production_volume";
            $groupBy = ['msc_code', 'vehicle_color_code', 'production_date', 'production_volume'];
        }

        return [$selectRaw, $groupBy];
    }

    /**
     * @param $params
     * @return void
     */
    private function buildProductionPlanVolumeFilter($params)
    {
        $this->addFilterImportFile($params, MrpProductionPlanImport::STATUS_RAN_MRP);
        if (isset($params['year']) && $this->checkParamYearFilter($params['year'])) {
            $year = $params['year'];
        } else {
            $year = Carbon::now()->year;
        }

        if (isset($params['month']) && $this->checkParamMonthFilter($params['month'])) {
            $month = $params['month'];
        } else {
            $month = Carbon::now()->month;
        }

        if (isset($params['group_by']) && $this->checkParamFilter($params['group_by'])) {
            $groupMscBy = $params['group_by'];
        } else {
            $groupMscBy = 'day';
        }

        if ($groupMscBy == 'day' || $groupMscBy == 'week') {
            $this->query->where(['year' => $year, 'month_no' => $month]);
        } else {
            $this->query->where(['year' => $year]);
        }

        if (isset($params['vehicle_color_code']) && $this->checkParamFilter($params['vehicle_color_code'])) {
            $this->whereLike('vehicle_color_code', $params['vehicle_color_code']);
        }

        $this->addFilterPlantCode($params, false);
    }
}
