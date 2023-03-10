<?php

namespace App\Services;

use App\Helpers\ImportHelper;
use App\Models\Procurement;
use App\Models\Remark;
use App\Models\Warehouse;
use App\Models\WarehouseSummaryAdjustment;
use App\Models\WarehouseInventorySummary;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseSummaryAdjustmentService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return WarehouseSummaryAdjustment::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy',
        'remarkable.updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {

        if (isset($params['warehouse_code']) && $this->checkParamFilter($params['warehouse_code'])) {
            $this->whereLike('warehouse_code', $params['warehouse_code']);
        }

        if (isset($params['part_code']) && $this->checkParamFilter($params['part_code'])) {
            $this->whereLike('part_code', $params['part_code']);
        }

        $this->addFilterPlantCode($params);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @param bool $hasRemark
     * @return array
     * @throws Exception
     */
    public function store(array $attributes, bool $hasRemark = true): array
    {
        $whSummary = WarehouseInventorySummary::query()
            ->where([
                'warehouse_code' => $attributes['warehouse_code'],
                'plant_code' => $attributes['plant_code'],
                'part_code' => $attributes['part_code'],
                'part_color_code' => $attributes['part_color_code'],
            ])
            ->first();

        $warehouse = Warehouse::query()->where(['code' => $attributes['warehouse_code']])->first();
        $warehouse_type = $warehouse->warehouse_type ?: Warehouse::TYPE_BWH;

        DB::beginTransaction();
        list($old_quantity, $new_quantity, $whSummaryRows) = $this->handleData($whSummary, $attributes, $warehouse_type,
            auth()->id(), Carbon::now()->toDateTimeString(), [], true);
        $attributes['old_quantity'] = $old_quantity;
        $attributes['new_quantity'] = $new_quantity;
        if ($new_quantity < 0) {
            DB::rollBack();
            return [false, "The New Quantity must not be smaller than 0."];
        }
        if (count($whSummaryRows)) {
            WarehouseInventorySummary::query()->insert($whSummaryRows);
        }
        DB::commit();
        $parent = $this->query->create($attributes);
        $this->createRemark($parent);
        return [$parent, null];
    }

    /**
     * @param $dataInsert
     * @param $uniqueKeys
     * @param $uniqueData
     * @param $warehouseCodes
     * @param bool $autoCreateRemark
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public function insertBulk($dataInsert, $uniqueKeys, $uniqueData, $warehouseCodes, bool $autoCreateRemark = false, array &$failures = [])
    {
        $whSummaryRows = [];
        $rowsInsert = [];
        $loggedId = auth()->id();
        $currentTime = Carbon::now()->toDateTimeString();

        $rowsExists = [];
        $inventorySummaries = WarehouseInventorySummary::whereInMultiple($uniqueKeys, $uniqueData)
            ->get();
        foreach ($inventorySummaries as $log) {
            $key = $this->convertDataToKey($uniqueKeys, $log);
            $rowsExists[$key] = $log;
        }

        $warehouseCodeType = Warehouse::query()
            ->whereIn('code', array_values($warehouseCodes))
            ->pluck('warehouse_type', 'code')
            ->toArray();

        foreach ($dataInsert as $data) {
            if ($data['adjustment_quantity'] == 0) continue;
            $keyByData = $this->convertDataToKey($uniqueKeys, $data);
            $whSummary = $rowsExists[$keyByData] ?? null;
            list($old_quantity, $new_quantity, $whSummaryRows) = $this->handleData($whSummary, $data,
                $warehouseCodeType[$data['warehouse_code']], $loggedId, $currentTime, $whSummaryRows, true);

            if ($new_quantity >= 0) {
                unset($data['row']);
                $rowsInsert[] = array_merge($data, [
                    'old_quantity' => $old_quantity,
                    'new_quantity' => $new_quantity,
                    'created_by' => $loggedId,
                    'updated_by' => $loggedId,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ]);
            } else {
                ImportHelper::processErrors(
                    $data['row'],
                    'Adjustment Quantity',
                    'The New Quantity must not be smaller than 0.',
                    [$new_quantity],
                    $failures
                );
            }
        }

        if (count($whSummaryRows)) {
            WarehouseInventorySummary::query()->insert($whSummaryRows);
        }

        if (count($rowsInsert)) {
            $this->query->insert($rowsInsert);
            if ($autoCreateRemark) $this->autoCreateRemark($rowsInsert, $loggedId, $currentTime);
        }
    }

    /**
     * @param $rowsInsert
     * @param $loggedId
     * @param $currentTime
     * @return void
     */
    private function autoCreateRemark($rowsInsert, $loggedId, $currentTime)
    {
        $keys = array_keys($rowsInsert[0]);
        $data = [];
        foreach ($rowsInsert as $rowInsert) {
            $data[] = array_map(function ($key) use ($rowInsert) {
                return $rowInsert[$key];
            }, $keys);
        }
        $ids = WarehouseSummaryAdjustment::whereInMultiple($keys, $data)
            ->select('id')->pluck('id')->toArray();
        $remarks = [];
        foreach ($ids as $id) {
            $remarks[] = [
                'modelable_type' => WarehouseSummaryAdjustment::class,
                'modelable_id' => $id,
                'content' => 'Automatically update quantity in Warehouse Summary',
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
        }
        Remark::query()->insert($remarks);
    }

    /**
     * @param $whSummary
     * @param $data
     * @param $warehouse_type
     * @param $loggedId
     * @param $currentTime
     * @param $whSummaryRows
     * @param bool $getUnit
     * @return array
     */
    private function handleData(
        $whSummary,
        $data,
        $warehouse_type,
        $loggedId,
        $currentTime,
        $whSummaryRows,
        bool $getUnit = false
    ): array {
        $adjustment_quantity = intval($data['adjustment_quantity']);
        if ($whSummary) {
            $data['old_quantity'] = $whSummary->quantity;
            $data['new_quantity'] = $whSummary->quantity + $adjustment_quantity;
            // update wh summary inventory log
            $whSummary->quantity = $data['new_quantity'];
            $whSummary->save();
        } else {
            $data['old_quantity'] = 0;
            $data['new_quantity'] = $adjustment_quantity;

            // create new wh summary inventory log
            // get unit from part procurement table
            $unit = null;
            if ($getUnit) {
                $partProcurement = Procurement::query()->where([
                    'plant_code' => $data['plant_code'],
                    'part_code' => $data['part_code'],
                    'part_color_code' => $data['part_color_code']
                ])->first();
                $unit = $partProcurement ? $partProcurement->unit : null;
            }
            $whSummaryRows[] = [
                'warehouse_code' => $data['warehouse_code'],
                'plant_code' => $data['plant_code'],
                'part_code' => $data['part_code'],
                'part_color_code' => $data['part_color_code'],
                'quantity' => $adjustment_quantity,
                'unit' => $unit,
                'warehouse_type' => $warehouse_type,
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
        }

        return [$data['old_quantity'], $data['new_quantity'], $whSummaryRows];
    }
}
