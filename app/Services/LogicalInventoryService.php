<?php

namespace App\Services;

use App\Models\LogicalInventory;
use App\Models\MrpProductionPlanImport;
use App\Models\MrpWeekDefinition;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LogicalInventoryService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return LogicalInventory::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        if (isset($params['part_code']) && $this->checkParamFilter($params['part_code'])) {
            $this->whereLike('logical_inventories.part_code', $params['part_code']);
        }

        if (isset($params['part_color_code']) && $this->checkParamFilter($params['part_color_code'])) {
            $this->whereLike('logical_inventories.part_color_code', $params['part_color_code']);
        }

        if (isset($params['plant_code']) && $this->checkParamFilter($params['plant_code'])) {
            $this->whereLike('logical_inventories.plant_code', $params['plant_code']);
        }

        if (isset($params['date']) && $this->checkParamDateFilter($params['date'])) {
            $this->query->whereDate('logical_inventories.production_date', '=', $params['date']);
        }

        if (isset($params['received_date']) && $this->checkParamDateFilter($params['received_date'])) {
            $this->query->where('production_date', '=', $params['received_date']);
        }

        if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
            $this->query->leftJoin('parts', function ($join) {
                $join->on('logical_inventories.part_code', '=', 'parts.code');
            });
            $this->whereLike('group', $params['part_group']);
        }

    }

    /**
     * @param bool $isPaginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function getCurrentSummary(bool $isPaginate = true)
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);
        $this->query->selectRaw("
                logical_inventories.part_code,
                logical_inventories.part_color_code,
                logical_inventories.plant_code,
                GROUP_CONCAT(logical_inventories.quantity SEPARATOR ',') as logical_quantities,
                GROUP_CONCAT(warehouse_inventory_summaries.quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT( warehouse_inventory_summaries.warehouse_type SEPARATOR ',') as warehouse_types
            ")->leftJoin('warehouse_inventory_summaries', function ($join) {
            $join->on('logical_inventories.part_code', '=', 'warehouse_inventory_summaries.part_code')
                ->on('logical_inventories.part_color_code', '=', 'warehouse_inventory_summaries.part_color_code');
        });
        $this->buildBasicQuery($params);
        $this->query
            ->groupBy(['logical_inventories.part_code', 'logical_inventories.part_color_code', 'logical_inventories.plant_code']);
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
    public function getForecastInventory(bool $isPaginate = true)
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);
        $this->query
            ->selectRaw("
                part_code,
                part_color_code,
                logical_inventories.plant_code,
                GROUP_CONCAT(quantity SEPARATOR ',') as quantities,
                GROUP_CONCAT(production_date SEPARATOR ',') as days
            ")
            ->where('production_date', '>=', Carbon::now()->addDay()->toDateString());
        $this->buildBasicQuery($params);
        $this->buildDateFilter($params);

        $this->query->groupBy(['part_code', 'part_color_code', 'logical_inventories.plant_code'])
            ->orderBy('part_code');

        if ($isPaginate) {
            return $this->query->paginate($limit);
        } else {
            return $this->query->get();
        }
    }

    /**
     * @param $params
     * @return void
     */
    private function buildDateFilter($params)
    {
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

        $mrpWeeks = MrpWeekDefinition::query()
            ->select('date')
            ->where([
                'year' => $year,
                'month_no' => $month
            ])
            ->latest('date')
            ->pluck('date')
            ->toArray();
        $this->query
            ->where('production_date', '>=', $mrpWeeks[array_key_last($mrpWeeks)])
            ->where('production_date', '<=', $mrpWeeks[0]);
    }

    /**
     * @return Builder|Model|object|null
     */
    public function latestImportFile()
    {
        return MrpProductionPlanImport::query()
            ->select('original_file_name')
            ->where('mrp_or_status', MrpProductionPlanImport::STATUS_RAN_MRP)
            ->where('mrp_or_progress', '=', 100)
            ->latest('updated_at')
            ->first();
    }

}
