<?php

namespace App\Services;

use App\Models\MrpProductionPlanImport;
use App\Models\ShortagePart;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ShortagePartService extends BaseService
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
        return ShortagePart::class;
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
        $this->addFilterImportFile($params, MrpProductionPlanImport::STATUS_CHECKED_SHORTAGE);
        $this->addFilterMrpWeekDefinitionMonthYear($params);

        if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
            $this->query->leftJoin('parts', function ($join) {
                $join->on('shortage_parts.part_code', '=', 'parts.code');
            });
            $this->query->where('group', $params['part_group']);
        }
        $this->addFilterPartAndPartColor($params);
        $this->currentFilterImport = $params['import_file'] ?? null;
    }

    /**
     * @param bool $isPaginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function filterShortagePart(bool $isPaginate = true)
    {
        DB::statement('SET SESSION group_concat_max_len = 10000');
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        $this->query->selectRaw("
            part_code,
            part_color_code,
            import_id,
            shortage_parts.plant_code,
            GROUP_CONCAT(quantity SEPARATOR ',') as quantities,
            GROUP_CONCAT(plan_date SEPARATOR ',') as days
        ")->join('mrp_week_definitions', 'shortage_parts.plan_date', '=', 'mrp_week_definitions.date');

        $this->buildBasicQuery($params);
        $this->query
            ->groupBy(['part_code', 'part_color_code', 'shortage_parts.plant_code', 'import_id'])
            ->orderBy('part_code');
        if ($isPaginate) {
            return $this->query->paginate($limit);
        } else {
            return $this->query->get();
        }
    }

    /**
     * @param $partCode
     * @param $partColorCode
     * @param $planDate
     * @param $plantCode
     * @param $importId
     * @return void
     */
    public function addRemark($partCode, $partColorCode, $planDate, $plantCode, $importId)
    {
        $shortagePart = $this->query
            ->where([
                'part_code' => $partCode,
                'part_color_code' => $partColorCode,
                'plan_date' => $planDate,
                'plant_code' => $plantCode,
                'import_id' => $importId
            ])
            ->firstOrFail();
        $this->createRemark($shortagePart);
    }

    /**
     * @param $shortageParts
     * @return array
     */
    public function getRemarks($shortageParts): array
    {
        $uniqueKeys = ['part_code', 'part_color_code', 'plan_date', 'plant_code', 'import_id'];
        $uniqueData = [];
        foreach ($shortageParts as $shortagePart) {
            $planDates = explode(',', $shortagePart->days);
            foreach ($planDates as $planDate) {
                $uniqueData[] = [
                    $shortagePart->part_code, $shortagePart->part_color_code,
                    $planDate, $shortagePart->plant_code, $shortagePart->import_id
                ];
            }
        }
        if (!count($uniqueData)) return [];
        $shortagePartWithRemarks = ShortagePart::whereInMultiple($uniqueKeys, $uniqueData)
            ->with('remarkable.updatedBy')
            ->whereHas('remarkable')
            ->get();
        $remarks = [];
        foreach ($shortagePartWithRemarks as $shortagePartWithRemark) {
            $key = implode('-', [
                $shortagePartWithRemark->part_code, $shortagePartWithRemark->part_color_code,
                $shortagePartWithRemark->plant_code, $shortagePartWithRemark->import_id
            ]);
            $remarks[$key][$shortagePartWithRemark->plan_date] = $this->transformRemark($shortagePartWithRemark->remarkable);
        }
        return $remarks;
    }

    /**
     * @param $remarks
     * @return array
     */
    private function transformRemark($remarks): array
    {
        $data = [];
        foreach ($remarks as $remark) {
            $data[] = [
                'id' => $remark->id,
                'content' => $remark->content,
                'created_at' => $remark->created_at->toIso8601String(),
                'updated_at' => $remark->updated_at->toIso8601String(),
                'user' => [
                    'id' => $remark->updatedBy->id,
                    'code' => $remark->updatedBy->username,
                    'username' => $remark->updatedBy->name
                ]
            ];
        }
        return $data;
    }

    /**
     * @param $importId
     * @param $mrpRunDate
     * @return array
     */
    public function simulationRun($importId, $mrpRunDate): array
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
            ->post('/mrp/v1/simulation_run', $data);

        if ($response->status() == 200) {
            return [true, null];
        } else {
            return [false, $response->body()];
        }
    }
}
