<?php

namespace App\Services;

use App\Models\MrpProductionPlanImport;
use App\Models\ProductionPlan;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductionPlanService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return ProductionPlan::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'msc'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        $this->addFilterImportFile($params);
        $this->addFilterMrpWeekDefinitionMonthYear($params);

        if (isset($params['msc_code']) && $this->checkParamFilter($params['msc_code'])) {
            $this->whereLike('msc_code', $params['msc_code']);
        }

        if (isset($params['vehicle_color_code']) && $this->checkParamFilter($params['vehicle_color_code'])) {
            $this->whereLike('vehicle_color_code', $params['vehicle_color_code']);
        }

    }

    /**
     * @param bool $isPaginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function filterProductionPlant(bool $isPaginate = true)
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);
        $this->query->selectRaw("
            msc_code, vehicle_color_code, production_plans.plant_code,
            GROUP_CONCAT(plan_date SEPARATOR ',') as days,
            GROUP_CONCAT(volume SEPARATOR ',') as volumes
        ")->join('mrp_week_definitions', 'production_plans.plan_date', '=', 'mrp_week_definitions.date');
        $this->buildBasicQuery($params);
        $this->query
            ->groupBy(['msc_code', 'vehicle_color_code', 'production_plans.plant_code'])
            ->latest('msc_code');
        if ($isPaginate) {
            return $this->query->paginate($limit);
        } else {
            return $this->query->get();
        }
    }

    /**
     * @param $importId
     * @return array
     */
    public static function validateProductionPlan($importId): array
    {
        $hasProductionPlanOutEffectiveDate = ProductionPlan::query()
            ->join('mscs', function($join) {
                $join->on('mscs.code', '=', 'production_plans.msc_code')
                    ->on('mscs.plant_code', '=', 'production_plans.plant_code');
            })
            ->where('import_id', $importId)
            ->whereRaw('(production_plans.plan_date < mscs.effective_date_in or production_plans.plan_date > mscs.effective_date_out)')
            ->count();
        if ($hasProductionPlanOutEffectiveDate) {
            return [false, 'Having MSC in the production plan were changed the effective date'];
        }

        $setting = Setting::query()->where('key', 'max_product')->first();
        $maxVolume = $setting ? $setting->value[0] : 1000;
        $hasPlanDateOverMaxVolume = ProductionPlan::query()
            ->selectRaw('plan_date, sum(volume) as total_volume')
            ->where('import_id', $importId)
            ->groupBy('plan_date')
            ->having('total_volume', '>', $maxVolume)
            ->count();

        if ($hasPlanDateOverMaxVolume) {
            return [false, 'The production quantity of the day exceeds the maximum number of cars produced'];
        }

        $hasNotWorkingDay = ProductionPlan::query()
            ->join('mrp_week_definitions', 'production_plans.plan_date', '=', 'mrp_week_definitions.date')
            ->where('import_id', $importId)
            ->where('day_off', true)
            ->where('volume',  '>', '0')
            ->count();
        if ($hasNotWorkingDay) {
            return [false, 'Having an invalid plan date is a holiday.'];
        }

        return [true, null];
    }
}
