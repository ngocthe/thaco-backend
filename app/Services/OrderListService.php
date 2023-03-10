<?php

namespace App\Services;

use App\Constants\MRP;
use App\Models\MrpOrderCalendar;
use App\Models\MrpProductionPlanImport;
use App\Models\MrpWeekDefinition;
use App\Models\OrderList;
use App\Models\PartGroup;
use App\Models\ShortagePart;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OrderListService extends BaseService
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
        return OrderList::class;
    }

    /**
     * @var array|string[]
     */
    public array $defaultRelations = [
        'updatedBy', 'remarkable.updatedBy'
    ];

    /**
     * @param $relations
     * @return void
     */
    public function setDefaultRelations($relations)
    {
        $this->defaultRelations = $relations;
    }

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        if (isset($params['status']) && $this->checkParamFilter($params['status'])) {
            $this->query->where('status', $params['status']);
        }

        if (isset($params['eta']) && $this->checkParamFilter($params['eta'])) {
            $this->query->where('eta', $params['eta']);
        }

        if (isset($params['is_popup_release']) && $params['is_popup_release']) {
            if (isset($params['contract_code']) && $this->checkParamFilter($params['contract_code'])) {
                $this->query->where('contract_code', $params['contract_code']);
            }

            if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
                $this->query->where('part_group', $params['part_group']);
            }

            if (isset($params['supplier_code']) && $this->checkParamFilter($params['supplier_code'])) {
                $this->query->where('supplier_code', $params['supplier_code']);
            }

        } else {
            if (isset($params['contract_code']) && $this->checkParamFilter($params['contract_code'])) {
                $this->whereLike('contract_code', $params['contract_code']);
            }

            if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
                $this->whereLike('part_group', $params['part_group']);
            }

            if (isset($params['supplier_code']) && $this->checkParamFilter($params['supplier_code'])) {
                $this->whereLike('supplier_code', $params['supplier_code']);
            }
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);

    }

    /**
     * @param $attributes
     * @return array|string[]
     */
    public function mergeDataStore($attributes): array
    {
        $mergedAttributes = ['eta' => '', 'status' => MRP::MRP_ORDER_LIST_STATUS_WAIT];

        if (isset($attributes['contract_code'])) {
            $mrpOrderCalendarObj = MrpOrderCalendar::query()->where('contract_code', '=', $attributes['contract_code'])->first();
            if ($mrpOrderCalendarObj) {
                $mergedAttributes['eta'] = $mrpOrderCalendarObj->eta;
            } else {
                abort(404);
            }
        }

        return array_merge($attributes, $mergedAttributes);
    }

    /**
     * @param $attributes
     * @return bool
     */
    public function validateGroupKeyWithEtaUnique($attributes): bool
    {
        $existOrder = $this->findBy(
            [
                'contract_code' => $attributes['contract_code'],
                'part_code' => $attributes['part_code'],
                'part_color_code' => $attributes['part_color_code'],
                'eta' => $attributes['eta'],
                'plant_code' => $attributes['plant_code']
            ]
        );

        if ($existOrder) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return void
     */
    public function buildQueryOrderList()
    {
        $this->query->whereIn('status', [MRP::MRP_ORDER_LIST_STATUS_WAIT, MRP::MRP_ORDER_LIST_STATUS_DONE]);
    }

    /**
     * @param $params
     * @param array $relations
     * @param bool $withTrashed
     * @return LengthAwarePaginator
     */
    public function paginateWithDefaultAttribute($params = null, array $relations = [], bool $withTrashed = false): LengthAwarePaginator
    {
        self::buildQueryImportFile();
        self::buildQueryOrderList();

        $attributes = request()->except(['import_id']);

        return parent::paginate($attributes, $relations, $withTrashed); // TODO: Change the autogenerated stub
    }

    /**
     * @param $params
     * @param array $relations
     * @param bool $withTrashed
     * @return LengthAwarePaginator
     */
    public function paginateWithDeliveringStatus($params = null, array $relations = [], bool $withTrashed = false): LengthAwarePaginator
    {
        self::buildQueryImportFile();

        $attributes = request()->except(['import_id']);

        return parent::paginate($attributes, $relations, $withTrashed); // TODO: Change the autogenerated stub
    }

    /**
     * @param $attributes
     */
    public function release($attributes)
    {

        DB::beginTransaction();
        $this->query
            ->where('contract_code', '=', $attributes['contract_code'])
            ->where('supplier_code', '=', $attributes['supplier_code'])
            ->where('status', '=', MRP::MRP_ORDER_LIST_STATUS_WAIT);

        if (isset($attributes['part_group'])) {
            $this->query->where('part_group', '=', $attributes['part_group']);
        }

        $orderList = $this->query->update([
            'status' => MRP::MRP_ORDER_LIST_STATUS_RELEASE
        ]);

        if (!$orderList) {
            abort(404);
        }

        $mrpOrderCalendarQuery = MrpOrderCalendar::query()
            ->where(['mrp_order_calendars.status' => MRP::MRP_ORDER_CALENDAR_STATUS_WAIT, 'mrp_order_calendars.contract_code' => $attributes['contract_code']])
            ->join('order_lists', function ($join) {
                $join->on('mrp_order_calendars.contract_code', '=', 'order_lists.contract_code')
                    ->on('mrp_order_calendars.part_group', '=', 'order_lists.part_group');
            })
            ->where('order_lists.contract_code', '=', $attributes['contract_code'])
            ->where('order_lists.supplier_code', '=', $attributes['supplier_code']);

        if (isset($attributes['part_group'])) {
            $mrpOrderCalendarQuery->where('order_lists.part_group', '=', $attributes['part_group']);
        }

        $mrpOrderCalendarQuery->update(['mrp_order_calendars.status' => MRP::MRP_ORDER_CALENDAR_STATUS_DONE]);

        DB::commit();

    }

    /**
     * @param $attributes
     * @return array|mixed
     */
    public function buildAttributeDefault($attributes)
    {
        $latestOrder = null;

        $queryBase = OrderList::query()->whereIn('status', [MRP::MRP_ORDER_LIST_STATUS_WAIT, MRP::MRP_ORDER_LIST_STATUS_DONE]);

        if (empty($attributes['contract_code'])) {
            /**
             * @var OrderList $latestOrder
             */
            $latestOrder = self::__getFirstLatestOrder($queryBase);
            $attributes = array_merge($attributes, ['contract_code' => $latestOrder->contract_code]);
        }

        if (empty($attributes['supplier_code'])) {
            if (!$latestOrder) {
                $latestOrder = self::__getFirstLatestOrder($queryBase);
            }
            $attributes = array_merge($attributes, ['supplier_code' => $latestOrder->supplier_code]);
        }

        if (empty($attributes['plant_code'])) {
            if (!$latestOrder) {
                $latestOrder = self::__getFirstLatestOrder($queryBase);
            }
            $attributes = array_merge($attributes, ['plant_code' => $latestOrder->plant_code]);
        }

        return $attributes;
    }

    /**
     * @return void
     */
    public function buildQueryImportFile()
    {
        $importId = request()->get('import_id');

        if ($importId == '-1') {
            $this->query->whereNull('import_id');
        } elseif (isset($importId) && $this->checkParamFilter($importId)) {
            $this->query->where('import_id', $importId);
            $importFile = MrpProductionPlanImport::query()->where('id', $importId)->first();
            $this->currentFilterImport = [
                'id' => $importFile->id,
                'original_file_name' => $importFile->original_file_name,
                'mrp_or_result' => $importFile->mrp_or_result,
                'mrp_or_status' =>  $importFile->mrp_or_status
            ];
        }

    }

    /**
     * @param Builder|null $queryBase
     * @return Model
     */
    private function __getFirstLatestOrder(Builder $queryBase = null): Model
    {
        if (!$queryBase) {
            $queryBase = OrderList::query();
        }
        return $queryBase->latest()->firstOrFail();
    }

    /**
     * @param $parent
     * @param array $attributes
     * @param bool $hasRemark
     * @return bool|Model|int
     * @throws Exception
     */
    public function update($parent, array $attributes, bool $hasRemark = true)
    {
        $this->query->where('status', '=', MRP::MRP_ORDER_LIST_STATUS_WAIT);
        return parent::update($parent, $attributes, $hasRemark);
    }

    /**
     * @param $item
     * @param bool $force
     * @return array|bool
     */
    public function destroy($item, bool $force = false)
    {
        $this->query->where('status', '=', MRP::MRP_ORDER_LIST_STATUS_WAIT);
        return parent::destroy($item, $force);
    }

    /**
     * @param $importId
     * @param $contractCode
     * @param $partGroup
     * @return int
     */
    public function checkShortagePart($importId, $contractCode, $partGroup): int
    {
        // get Target Plan From
        $mrpOrderCalendar = MrpOrderCalendar::query()
            ->where([
                'contract_code' => $contractCode,
                'part_group' => $partGroup
            ])
            ->first();
        $targetPlanFrom = explode('-', $mrpOrderCalendar->target_plan_from); // ex W5-08/2022
        $weekNo = str_replace('W', '', $targetPlanFrom[0]); // ex: W5
        $month = explode('/', $targetPlanFrom[1]); // [08, 2022]
        $monthNo = $month[0];
        $year = $month[1];
        $targetPlanFromDate = MrpWeekDefinition::query()
            ->where([
                'year' => $year,
                'month_no' => $monthNo,
                'week_no' => $weekNo
            ])
            ->orderBy('date')
            ->first();
        $leadTime = $mrpOrderCalendar->mrp_or_run;

        $rsl = ShortagePart::query()
            ->selectRaw('SUM(quantity) as total_quantity')
            ->where('import_id', $importId)
            ->where('plan_date' , '>=', $leadTime)
            ->where('plan_date', '<', $targetPlanFromDate->date)
            ->first();
        return $rsl->total_quantity;
    }

    /**
     * @param $importId
     * @param $contractCode
     * @param $partGroup
     * @param $mrpRunDate
     * @return array
     */
    public function orderRun($importId, $contractCode, $partGroup, $mrpRunDate): array
    {
        $mrpRunDate = Carbon::createFromFormat('n/d/Y', $mrpRunDate)->toDateString();
        list($status, $message) = ProductionPlanService::validateProductionPlan($importId);
        if (!$status) {
            return [$status, $message];
        }

        $data = [
            'production_plan_id' => $importId,
            'user_code' => auth()->id(),
            'plant_code' => MRP::DEFAULT_PLANT_CODE,
            'mrp_run_date' => $mrpRunDate,
            'contract_code' => $contractCode,
            'part_group' => $partGroup
        ];

        $response = Http::baseUrl(config('env.MRP_URL'))
            ->asForm()
            ->post('/mrp/v1/order_run', $data);

        if ($response->status() == 200) {
            return [true, null];
        } else {
            return [false, $response->body()];
        }
    }

    /**
     * @param bool $isDeliveringOrder
     * @return array
     */
    public function getOrderListGroupByContract(bool $isDeliveringOrder = false): array
    {
        $this->buildQueryImportFile();
        if(!$isDeliveringOrder) {
            $this->buildQueryOrderList();
        }
        $this->buildBasicQuery(request()->except(['import_id']));

        $this->query->join(DB::raw('(SELECT target_plan_to, contract_code AS contract_code_mrp, part_group AS part_group_mrp FROM mrp_order_calendars) AS mrp'), function ($builder) {
            $builder->on('order_lists.contract_code', 'contract_code_mrp')
                ->on('order_lists.part_group', 'part_group_mrp');
        });

        $rows = $this->query->with('part')
            ->orderBy('contract_code')
            ->orderBy('eta')
            ->orderBy('part_group')
            ->orderBy('supplier_code')
            ->orderBy('plant_code')
            ->get()
            ->toArray();
        $orderLists = [];
        $uniqueKeys = ['contract_code', 'eta', 'part_group', 'plant_code', 'supplier_code'];
        foreach ($rows as $row) {
            $key = $this->convertDataToKey($uniqueKeys, $row);
            $orderLists[$key][] = $row;
        }
        return $orderLists;
    }



}
