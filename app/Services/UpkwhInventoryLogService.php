<?php

namespace App\Services;

use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\DefectInventory;
use App\Models\UpkwhInventoryLog;
use App\Models\WarehouseInventorySummary;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UpkwhInventoryLogService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return UpkwhInventoryLog::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy',
        'remarkable.updatedBy',
        'defectable.remarkable'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {

        if (isset($params['contract_code']) && $this->checkParamFilter($params['contract_code'])) {
            $this->whereLike('contract_code', $params['contract_code']);
        }

        if (isset($params['invoice_code']) && $this->checkParamFilter($params['invoice_code'])) {
            $this->whereLike('invoice_code', $params['invoice_code']);
        }

        if (isset($params['bill_of_lading_code']) && $this->checkParamFilter($params['bill_of_lading_code'])) {
            $this->whereLike('bill_of_lading_code', $params['bill_of_lading_code']);
        }

        if (isset($params['container_code']) && $this->checkParamFilter($params['container_code'])) {
            $this->whereLike('container_code', $params['container_code']);
        }

        if (isset($params['case_code']) && $this->checkParamFilter($params['case_code'])) {
            $this->whereLike('case_code', $params['case_code']);
        }

        if (isset($params['box_type_code']) && $this->checkParamFilter($params['box_type_code'])) {
            $this->whereLike('box_type_code', $params['box_type_code']);
        }

        if (isset($params['supplier_code']) && $this->checkParamFilter($params['supplier_code'])) {
            $this->whereLike('supplier_code', $params['supplier_code']);
        }

        if (isset($params['received_date']) && $this->checkParamDateFilter($params['received_date'])) {
            $this->query->where('received_date', $params['received_date']);
        }

        if (isset($params['shelf_location_code']) && $this->checkParamFilter($params['shelf_location_code'])) {
            $this->whereLike('shelf_location_code', $params['shelf_location_code']);
        }

        if (isset($params['shipped_date']) && $this->checkParamDateFilter($params['shipped_date'])) {
            $this->query->where('shipped_date', $params['shipped_date']);
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);
        $this->addFilterDefect($params);
        $this->addFilterUpdatedAt($params);

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
        $uniqueKeys = [
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'part_code',
            'part_color_code',
            'box_type_code',
            'plant_code'
        ];
        $uniqueData = [[
            $attributes['contract_code'],
            $attributes['invoice_code'],
            $attributes['bill_of_lading_code'],
            $attributes['container_code'],
            $attributes['case_code'],
            $attributes['part_code'],
            $attributes['part_color_code'],
            $attributes['box_type_code'],
            $attributes['plant_code'],
        ]];

        $bwhOrderRequest = BwhOrderRequest::whereInMultiple($uniqueKeys, $uniqueData)->first();
        if ($bwhOrderRequest) {
            return [false, 'There is already a bonded order request'];
        }

        /**
         * @var BwhInventoryLog $bwhLog
         */
        $bwhLog = BwhInventoryLog::whereInMultiple($uniqueKeys, $uniqueData)
            ->where('requested', false)
            ->whereNotNull('shipped_date')
            ->orderBy('stored_date')
            ->orderBy('created_at')
            ->first();
        if ($bwhLog) {
            return $this->updateAndSaveData($bwhLog, $attributes, $attributes['box_quantity'], $hasRemark);
        } else {
            return [false, 'There is no corresponding data in bonded inventory'];
        }
    }

    /**
     * @param BwhInventoryLog $bwhLog
     * @param $attributes
     * @param $boxQuantity
     * @param $hasRemark
     * @return array
     */
    public function updateAndSaveData(BwhInventoryLog $bwhLog, $attributes, $boxQuantity, $hasRemark): array
    {
        // update Date Case shipped to UPKWH
        if (!$bwhLog->requested) {
            $bwhLog->requested = true;
            $bwhLog->save();
        }

        // copy data from bwh to upkwh
        $attributes['box_quantity'] = $boxQuantity;
        $attributes['part_quantity'] = $bwhLog->part_quantity;
        $attributes['unit'] = $bwhLog->unit;
        $attributes['supplier_code'] = $bwhLog->supplier_code;

        $upkwhLog = new UpkwhInventoryLog();
        $upkwhLog = $upkwhLog->fill($attributes);
        $upkwhLog->save();

        if ($hasRemark) {
            $this->createRemark($upkwhLog);
        }

        // update warehouse summary
        if ($upkwhLog->warehouse_code && !$upkwhLog->defect_id) {
            $row = $upkwhLog->toArray();
            $row['quantity'] = $upkwhLog->box_quantity * $upkwhLog->part_quantity;
            $row['warehouse_type'] = WarehouseInventorySummary::TYPE_UPKWH;
            // update or insert to upwk summary
            WarehouseInventorySummaryService::insertOrUpdateBulk($row['warehouse_type'], [$row]);
        } elseif ($upkwhLog->defect_id) {
            $this->saveUpkwhInventoryDefect($upkwhLog, $upkwhLog->box_quantity);
        }

        return [$upkwhLog, ''];
    }

    /**
     * @param UpkwhInventoryLog $upkwhLog
     * @param $boxQuantity
     * @return void
     */
    protected function saveUpkwhInventoryDefect(UpkwhInventoryLog $upkwhLog, $boxQuantity)
    {
        $defectRows = [];
        $loggedId = auth()->id();
        $now = Carbon::now()->toDateTimeString();
        for ($i = 1; $i <= $boxQuantity; $i++) {
            $defectRows[] = [
                'modelable_type' => UpkwhInventoryLog::class,
                'modelable_id' => $upkwhLog->id,
                'defect_id' => $upkwhLog->defect_id,
                'box_id' => $i,
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DefectInventory::query()->insert($defectRows);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|int $parent
     * @param array $attributes
     * @param bool $hasRemark
     * @return array
     * @throws Exception
     */
    public function update($parent, array $attributes, bool $hasRemark = true): array
    {
        /**
         * @var UpkwhInventoryLog $upkLog
         */
        $upkLog = $this->query->findOrFail($parent);

        if (isset($attributes['shipped_box_quantity']) && $upkLog->shipped_box_quantity && $attributes['shipped_box_quantity'] < $upkLog->shipped_box_quantity) {
            return [false, 'The Number of Boxes shipped to Assembly Plant must not be smaller than the current value'];
        }

        $oldWarehouseCode = $upkLog->getOriginal('warehouse_code');
        $oldShippedBoxQuantity = $upkLog->getOriginal('shipped_box_quantity', 0);
        $defectId = $upkLog->defect_id;

        $upkLog->fill($attributes);

        if ($upkLog->isDirty('shipped_box_quantity') && !$upkLog->shipped_date) {
            return [false, 'The shipped date field is required when shipped box quantity is present.'];
        }

        if ($upkLog->isDirty('shipped_date') && !$upkLog->shipped_box_quantity) {
            return [false, 'The Number of Boxes shipped to Assembly Plant must be a positive integer.'];
        }

        if ($upkLog->box_quantity < $upkLog->shipped_box_quantity) {
            return [false, 'The Number of Boxes shipped to Assembly Plant must smaller than or equal the Quantity of Box'];
        }

        if (!$defectId) {
            if (($oldWarehouseCode != $upkLog->warehouse_code)) {
                // update or insert to upwk summary
                $this->updateWarehouseInventorySummary($upkLog, $upkLog->warehouse_code);
                if ($oldWarehouseCode) {
                    $this->updateWarehouseInventorySummary($upkLog, $oldWarehouseCode, true);
                }
            }

            if ($upkLog->shipped_date && $upkLog->shipped_box_quantity && $upkLog->shipped_box_quantity != $oldShippedBoxQuantity) {
                $row = $upkLog->toArray();
                $row['quantity'] = -1 * ($upkLog->shipped_box_quantity - $oldShippedBoxQuantity) * $row['part_quantity'];
                $row['warehouse_type'] = WarehouseInventorySummary::TYPE_UPKWH;
                WarehouseInventorySummaryService::insertOrUpdateBulk($row['warehouse_type'], [$row]);
            }
        }

        $this->createRemark($upkLog);
        $upkLog->save();
        return [$upkLog, null];
    }

    /**
     * @param $upkLog
     * @param $warehouseCode
     * @param bool $isDecrement
     * @return void
     */
    private function updateWarehouseInventorySummary($upkLog, $warehouseCode, bool $isDecrement = false)
    {
        $row = $upkLog->toArray();
        $row['warehouse_code'] = $warehouseCode;
        $row['quantity'] = ($isDecrement ? -1 : 1) * $row['box_quantity'] * $row['part_quantity'];
        $row['warehouse_type'] = WarehouseInventorySummary::TYPE_UPKWH;
        WarehouseInventorySummaryService::insertOrUpdateBulk($row['warehouse_type'], [$row]);
    }

    /**
     * @param int $upkLogId
     * @param array $attributes
     * @return array
     * @throws Exception
     */
    public function defects(int $upkLogId, array $attributes): array
    {
        /**
         * @var UpkwhInventoryLog $upkLog
         */
        $upkLog = $this->query->findOrFail($upkLogId);

        if ($upkLog->shipped_box_quantity >= $upkLog->box_quantity) {
            return [false, 'Data cannot be updated once all boxes have been shipped'];
        }

        $upkLog->defect_id = $attributes['defect_id'];
        $upkLog->save();
        $this->saveBoxDefects($upkLog, $attributes['box_list']);
        $this->createRemark($upkLog);
        if ($upkLog->save()) {
            return [$upkLog, null];
        } else {
            return [false, 'Data cannot be updated'];
        }
    }

    /**
     * @param UpkwhInventoryLog $upkLog
     * @param array $boxList
     * @return void
     */
    private function saveBoxDefects(UpkwhInventoryLog $upkLog, array $boxList) {
        // delete all upkwh defects
        DefectInventory::query()
            ->where([
                'modelable_type' => UpkwhInventoryLog::class,
                'modelable_id' => $upkLog->id
            ])->forceDelete();
        $defectRows = [];
        $loggedId = auth()->id();
        $now = Carbon::now()->toDateTimeString();
        foreach ($boxList as $box) {
            $defectRows[] = [
                'modelable_type' => UpkwhInventoryLog::class,
                'modelable_id' => $upkLog->id,
                'defect_id' => $box['defect_id'],
                'box_id' => $box['id'],
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (count($defectRows))
            DefectInventory::query()->insert($defectRows);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $item
     * @param bool $force
     * @return bool
     *
     */
    public function destroy($item, bool $force = false): bool
    {
        /**
         * @var UpkwhInventoryLog $upkLog
         */
        $upkLog = $this->query->findOrFail($item);
        if ($upkLog->shipped_date) {
            return false;
        }

        $updateSummary = request()->get('update_summary', false);
        if ($updateSummary == 'true' || $updateSummary == 1) {
            $updateSummary = true;
        }

        DB::beginTransaction();
        try {
            if ($updateSummary && $upkLog->warehouse_code) {
                $this->createWarehouseAdjustment($upkLog);
            }
            // update bwh inventory log
            BwhInventoryLog::query()
                ->where([
                    'contract_code' => $upkLog->contract_code,
                    'invoice_code' => $upkLog->invoice_code,
                    'bill_of_lading_code' => $upkLog->bill_of_lading_code,
                    'container_code' => $upkLog->container_code,
                    'case_code' => $upkLog->case_code,
                    'part_code' => $upkLog->part_code,
                    'part_color_code' => $upkLog->part_color_code,
                    'box_type_code' => $upkLog->box_type_code,
                    'plant_code' => $upkLog->plant_code
                ])
                ->update(['requested' => false]);
            // delete bwh inventory logs
            $upkLog->delete();

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }
        DB::commit();
        return true;
    }

    /**
     * @param UpkwhInventoryLog $upkLog
     * @return void
     * @throws ValidationException
     */
    private function createWarehouseAdjustment(UpkwhInventoryLog $upkLog)
    {
        $uniqueKeys = [
            'warehouse_code',
            'part_code',
            'part_color_code',
            'plant_code'
        ];
        $warehouseCodes = [$upkLog->warehouse_code];
        $data = [
            'warehouse_code' => $upkLog->warehouse_code,
            'part_code' => $upkLog->part_code,
            'part_color_code' => $upkLog->part_color_code,
            'plant_code' => $upkLog->plant_code
        ];
        $uniqueData = [$data];
        // get adjustment_quantity
        $numberBoxDefect = DefectInventory::query()
            ->where([
                'modelable_type' => UpkwhInventoryLog::class,
                'modelable_id' => $upkLog->id,
            ])
            ->whereNotNull('defect_id')
            ->count();
        $data['adjustment_quantity'] = -1 * ($upkLog->box_quantity - $numberBoxDefect) * $upkLog->part_quantity;
        $data['row'] = '';
        (new WarehouseSummaryAdjustmentService())->insertBulk([$data], $uniqueKeys, $uniqueData, $warehouseCodes, true);
    }
}
