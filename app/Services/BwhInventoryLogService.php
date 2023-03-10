<?php

namespace App\Services;

use App\Constants\MRP;
use App\Helpers\ImportHelper;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\InTransitInventoryLog;
use App\Models\OrderList;
use App\Models\UpkwhInventoryLog;
use App\Models\WarehouseInventorySummary;
use App\Models\WarehouseLocation;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\Failure;

class BwhInventoryLogService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return BwhInventoryLog::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'remarkable.updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        $this->query->where('is_parent_case', true);

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

        if (isset($params['devanned_date']) && $this->checkParamDateFilter($params['devanned_date'])) {
            $this->query->where('devanned_date', $params['devanned_date']);
        }

        if (isset($params['container_received']) && $this->checkParamDateFilter($params['container_received'])) {
            $this->query->where('container_received', '=', $params['container_received']);
        }

        if (isset($params['stored_date']) && $this->checkParamDateFilter($params['stored_date'])) {
            $this->query->where('stored_date', $params['stored_date']);
        }

        if (isset($params['warehouse_location_code']) && $this->checkParamFilter($params['warehouse_location_code'])) {
            $this->whereLike('warehouse_location_code', $params['warehouse_location_code']);
        }

        if (isset($params['warehouse_code']) && $this->checkParamFilter($params['warehouse_code'])) {
            $this->whereLike('warehouse_code', $params['warehouse_code']);
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
     * @return array
     */
    public function searchCase(): array
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        if (isset($params['code']) && $this->checkParamFilter($params['code'])) {
            $this->whereLike('case_code', $params['code']);
        }
        $this->query->whereNull('defect_id');
        $this->query->where(function ($query) {
            $query->where('requested', 0);
            $query->orWhereNull('shipped_date');
        });
        $this->addDefaultFilterCode($params);
        return $this->query
            ->select('case_code')
            ->distinct()
            ->orderBy('case_code')
            ->limit($limit)
            ->pluck('case_code')
            ->toArray();
    }

    /**
     * @return array
     */
    public function searchPart(): array
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        if (isset($params['code']) && $this->checkParamFilter($params['code'])) {
            $this->whereLike('part_code', $params['code']);
        }

        if (isset($params['plant_code']) && $this->checkParamFilter($params['plant_code'])) {
            $this->query->where('plant_code', $params['plant_code']);
        }

        $this->query->whereNull('defect_id');
        $this->query->where(function ($query) {
            $query->where('requested', 0);
            $query->orWhereNull('shipped_date');
        });
        $this->addDefaultFilterCode($params);
        return $this->query
            ->select('part_code')
            ->distinct()
            ->orderBy('part_code')
            ->limit($limit)
            ->pluck('part_code')
            ->toArray();
    }


    /**
     * @return array
     */
    public function searchPartColor(): array
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        if (isset($params['part_code']) && $this->checkParamFilter($params['part_code'])) {
            $this->whereLike('part_code', $params['part_code']);
        }

        if (isset($params['code']) && $this->checkParamFilter($params['code'])) {
            $this->whereLike('part_color_code', $params['code']);
        }
        $this->query->whereNull('defect_id');
        $this->query->where(function ($query) {
            $query->where('requested', 0);
            $query->orWhereNull('shipped_date');
        });

        $this->addDefaultFilterCode($params);
        return $this->query
            ->select('part_color_code')
            ->distinct()
            ->orderBy('part_color_code')
            ->limit($limit)
            ->pluck('part_color_code')
            ->toArray();
    }

    /**
     * @return array
     */
    public function getColumnValue(): array
    {
        $column = request()->get('column', 'code');
        $keyword = request()->get('keyword');
        $part_code = request()->get('part_code');
        $plant_code = request()->get('plant_code');
        $keywordId = request()->get('import_id');

        $limit = (int)(request()->get('per_page', 20));

        if (!in_array($column, $this->model->getFillable())) {
            return [];
        }

        if ($this->checkParamFilter($keyword)) {
            $this->whereLike($column, $keyword);
        }

        if (isset($part_code)) {
            $this->query->where('part_code', '=', $part_code);
        }

        if (isset($plant_code)) {
            $this->query->where('plant_code', '=', $plant_code);
        }

        if ($keywordId) {
            $this->query->where('import_id', '=', $keywordId);
        }

        return $this->query
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($limit)
            ->pluck($column)
            ->toArray();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @param bool $hasRemark
     * @return Model|bool
     * @throws Exception
     */
    public function store(array $attributes, bool $hasRemark = true)
    {
        list($uniqueKeys, $uniqueData) = $this->getUniqueKeysAndData($attributes);
        $inTransitLogs = InTransitInventoryLog::whereInMultiple($uniqueKeys, [$uniqueData])
            ->with('partInfo')
            ->get()
            ->toArray();
        if (count($inTransitLogs)) {
            $whSummaryRows = $bwhLogsInsert = [];
            $loggedId = auth()->id();
            $currentTime = Carbon::now()->toDateTimeString();
            $commonData = [
                'container_received' => $attributes['container_received'] ?? null,
                'devanned_date' => $attributes['devanned_date'] ?? null,
                'stored_date' => $attributes['stored_date'] ?? null,
                'shipped_date' => $attributes['shipped_date'] ?? null,
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
            $dateValidate = [[
                'container_received' => $commonData['container_received'],
                'devanned_date' => $commonData['devanned_date'],
                'stored_date' => $commonData['stored_date'],
                'shipped_date' => $commonData['shipped_date']
            ]];
            $fails = [];
            self::validationDatePairs($dateValidate, $fails, 'Y-m-d', true);

            DB::beginTransaction();
            $this->prepareDataForBwhInventoryAndWarehouseSummary($attributes, $commonData, $inTransitLogs, $bwhLogsInsert, $whSummaryRows);
            try {
                if (count($bwhLogsInsert)) {
                    $this->query->insert($bwhLogsInsert);
                }
                if (count($whSummaryRows)) {
                    WarehouseInventorySummaryService::insertOrUpdateBulk(WarehouseInventorySummary::TYPE_BWH, $whSummaryRows);
                }

                $parent = BwhInventoryLog::whereInMultiple($uniqueKeys, [$uniqueData])
                    ->where('is_parent_case', true)
                    ->first();

                self::updateStatusOrderList($inTransitLogs);
                $this->createRemark($parent);
            } catch (Exception $exception) {
                DB::rollBack();
                Log::error($exception);
                throw new HttpResponseException(response()->json(
                    [
                        'status' => false,
                        'message' => $exception->getMessage(),
                        'data' => []
                    ], 500));
            }
            DB::commit();

            return $parent;
        } else {
            return false;
        }
    }

    /**
     * @param array $inTransitLogs
     * @return void
     */
    private function updateStatusOrderList(array $inTransitLogs)
    {
        $data = [];
        foreach ($inTransitLogs as $inTransitLog) {
            if ($inTransitLog['part_info']) {
                $row = [$inTransitLog['contract_code'], $inTransitLog['part_info']['group'], $inTransitLog['plant_code']];
                $key = implode('-', $row);
                $data[$key] = $row;
            }
        }
        if (count($data)) {
            OrderList::whereInMultiple(['contract_code', 'part_group', 'plant_code'], array_values($data))
                ->where('status', '=', MRP::MRP_ORDER_LIST_STATUS_RELEASE)
                ->update(['status' => MRP::MRP_ORDER_LIST_STATUS_DONE]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|int $parent
     * @param array $attributes
     * @param bool $hasRemark
     * @return array
     *
     * @throws Exception
     */
    public function update($parent, array $attributes, bool $hasRemark = true): array
    {
        /**
         * @var BwhInventoryLog $bwhInvLog
         */
        $bwhInvLog = $this->query->where([
            'id' => $parent,
            'is_parent_case' => true
        ])->firstOrFail();

        $oldWarehouseCode = $bwhInvLog->getOriginal('warehouse_code');
        $oldDefectId = $bwhInvLog->getOriginal('defect_id');
        $oldShippedDate = $bwhInvLog->getOriginal('shipped_date');
        $bwhInvLog->fill($attributes);
        if ($bwhInvLog->shipped_date && $oldDefectId != $bwhInvLog->defect_id) {
            return [false, 'Unable to update defect status when the container has been shipped.'];
        }

        if (!$bwhInvLog->isDirty()) {
            $this->createRemark($bwhInvLog);
            return [$this->query->with('remarkable.updatedBy')->first(), ''];
        }

        $dateValidate = [[
            'container_received' => $bwhInvLog->container_received ? $bwhInvLog->container_received->toDateString() : null,
            'devanned_date' => $bwhInvLog->devanned_date ? $bwhInvLog->devanned_date->toDateString() : null,
            'stored_date' => $bwhInvLog->stored_date ? $bwhInvLog->stored_date->toDateString() : null,
            'shipped_date' => $bwhInvLog->shipped_date ? $bwhInvLog->shipped_date->toDateString() : null
        ]];
        $fails = [];
        self::validationDatePairs($dateValidate, $fails, 'Y-m-d', true);
        $this->validateWarehouseLocationCode($bwhInvLog->warehouse_code, $bwhInvLog->warehouse_location_code, $bwhInvLog->plant_code);
        $whSummaryRows = [];
        $dataUpdate = [
            'container_received' => $bwhInvLog->container_received,
            'devanned_date' => $bwhInvLog->devanned_date,
            'stored_date' => $bwhInvLog->stored_date,
            'shipped_date' => $bwhInvLog->shipped_date,
            'warehouse_code' => $bwhInvLog->warehouse_code,
            'warehouse_location_code' => $bwhInvLog->warehouse_location_code,
            'defect_id' => $bwhInvLog->defect_id,
        ];
        list($uniqueKeys, $uniqueData) = $this->getUniqueKeysAndData($bwhInvLog);
        $bwhLogs = BwhInventoryLog::whereInMultiple($uniqueKeys, [$uniqueData])->get()->toArray();

        DB::beginTransaction();
        try {
            BwhInventoryLog::whereInMultiple($uniqueKeys, [$uniqueData])->update($dataUpdate);
            if (!$oldShippedDate) {
                $this->prepareWarehouseSummaryRows($dataUpdate, $bwhLogs, $oldWarehouseCode, $oldDefectId, $whSummaryRows, (bool)$bwhInvLog->shipped_date);
                if (count($whSummaryRows)) {
                    WarehouseInventorySummaryService::insertOrUpdateBulk(WarehouseInventorySummary::TYPE_BWH, $whSummaryRows);
                }
                if ($oldWarehouseCode != $bwhInvLog->warehouse_code) {
                    BwhOrderRequest::query()
                        ->where([
                            'contract_code' => $bwhInvLog->contract_code,
                            'invoice_code' => $bwhInvLog->invoice_code,
                            'bill_of_lading_code' => $bwhInvLog->bill_of_lading_code,
                            'container_code' => $bwhInvLog->container_code,
                            'case_code' => $bwhInvLog->case_code,
                            'plant_code' => $bwhInvLog->plant_code
                        ])
                        ->update([
                            'warehouse_code' => $bwhInvLog->warehouse_code,
                            'warehouse_location_code' => $bwhInvLog->warehouse_location_code
                        ]);
                }
            }
            $this->checkAndReRunBathCreateBwhOrderRequest($oldDefectId, $bwhInvLog, $bwhLogs);
            $this->createRemark($bwhInvLog);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, $exception->getMessage()];
        }
        DB::commit();
        return [$this->query->with('remarkable.updatedBy')->first(), ''];
    }

    /**
     * @param $warehouseCode
     * @param $locationCode
     * @param $plantCode
     * @return void
     * @throws ValidationException
     */
    private function validateWarehouseLocationCode($warehouseCode, $locationCode, $plantCode)
    {
        if ($warehouseCode && $locationCode && $plantCode) {
            $exists = WarehouseLocation::query()
                ->where([
                    'warehouse_code' => $warehouseCode,
                    'code' => $locationCode,
                    'plant_code' => $plantCode
                ])->exists();
            if (!$exists) {
                $failures = [];
                ImportHelper::processErrors(0,
                    'warehouse_code',
                    'Warehouse Code, Location Code, Plant Code are not linked together',
                    [$warehouseCode],
                    $failures,
                    true
                );
                throw ValidationException::withMessages($failures);
            }
        }
    }

    /**
     * @param $oldDefectId
     * @param $bwhInvLog
     * @param $bwhLogs
     * @return void
     */
    private function checkAndReRunBathCreateBwhOrderRequest($oldDefectId, $bwhInvLog, $bwhLogs)
    {
        if (!$oldDefectId && $bwhInvLog->defect_id) {
            $partData = [];
            foreach ($bwhLogs as $bwhLog) {
                $partData[] = [
                    $bwhLog['part_code'],
                    $bwhLog['part_color_code'],
                    $bwhLog['plant_code']
                ];
            }
            (new BwhOrderRequestService())->runBathCreateBwhOrderRequest($partData);
        }
    }

    /**
     * @param $dataImport
     * @param $dataRef
     * @param $uniqueKeys
     * @param $uniqueData
     * @param array $failures
     * @return void
     */
    public function insertOrUpdateBulk($dataImport, $dataRef, $uniqueKeys, $uniqueData, array &$failures)
    {
        $whSummaryRows = [];
        $bwhLogsInsert = [];
        $loggedId = auth()->id();
        $currentTime = Carbon::now()->toDateTimeString();

        $rowsExists = [];
        $bwhLogs = BwhInventoryLog::whereInMultiple($uniqueKeys, $uniqueData)
            ->where('is_parent_case', true)
            ->get();
        foreach ($bwhLogs as $log) {
            $key = $this->convertDataToKey($uniqueKeys, $log);
            $rowsExists[$key] = $log;
        }

        foreach ($uniqueData as $key => $data) {
            $keyByData = implode('-', $data);
            $row = $dataImport[$key];
            $rowsRef = $dataRef[$keyByData];

            if (isset($rowsExists[$keyByData])) {
                $log = $rowsExists[$keyByData];
                $oldWarehouseCode = $log->getOriginal('warehouse_code');
                $oldShippedDate = $log->getOriginal('shipped_date');
                $oldDefectId = $log->getOriginal('defect_id');
                if ($oldWarehouseCode && $row['warehouse_code'] && $oldWarehouseCode != $row['warehouse_code']) {
                    $failures[] = new Failure(
                        $row['row'],
                        'Warehouse Code',
                        ['The warehouse code is invalid, the container has been saved to another warehouse'],
                        [
                            'value_error' => $row['warehouse_code'],
                            'value_import' => $row
                        ]
                    );
                } elseif ($oldShippedDate && $oldShippedDate->format('d/m/Y') != $row['shipped_date']) {
                    $failures[] = new Failure(
                        $row['row'],
                        'Date Case shipped to UPKWH',
                        ['The Date Case shipped to UPKWH is invalid, the container has been shipped'],
                        [
                            'value_error' => $row['shipped_date'],
                            'value_import' => $row
                        ]
                    );
                } else {
                    $dataUpdate = [
                        'container_received' => $row['container_received'] ? Carbon::createFromFormat('d/m/Y', $row['container_received'])->toDateString() : null,
                        'devanned_date' => $row['devanned_date'] ? Carbon::createFromFormat('d/m/Y', $row['devanned_date'])->toDateString() : null,
                        'stored_date' => $row['stored_date'] ? Carbon::createFromFormat('d/m/Y', $row['stored_date'])->toDateString() : null,
                        'shipped_date' => $row['shipped_date'] ? Carbon::createFromFormat('d/m/Y', $row['shipped_date'])->toDateString() : null,
                        'warehouse_code' => $row['warehouse_code'] ?: null,
                        'defect_id' => $row['defect_id'] ?: null,
                    ];
                    if ($row['warehouse_location_code']) $dataUpdate['warehouse_location_code'] = $row['warehouse_location_code'];
                    BwhInventoryLog::whereInMultiple($uniqueKeys, $uniqueData)
                        ->update($dataUpdate);
                    $this->prepareWarehouseSummaryRows($dataUpdate, $rowsRef, $oldWarehouseCode, $oldDefectId, $whSummaryRows);
                }
            } else {
                $commonData = [
                    'container_received' => (isset($row['container_received']) && $row['container_received']) ? Carbon::createFromFormat('d/m/Y', $row['container_received'])->toDateString() : null,
                    'devanned_date' => (isset($row['devanned_date']) && $row['devanned_date']) ? Carbon::createFromFormat('d/m/Y', $row['devanned_date'])->toDateString() : null,
                    'stored_date' => (isset($row['stored_date']) && $row['stored_date']) ? Carbon::createFromFormat('d/m/Y', $row['stored_date'])->toDateString() : null,
                    'shipped_date' => (isset($row['shipped_date']) && $row['shipped_date']) ? Carbon::createFromFormat('d/m/Y', $row['shipped_date'])->toDateString() : null,
                    'created_by' => $loggedId,
                    'updated_by' => $loggedId,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ];
                $this->prepareDataForBwhInventoryAndWarehouseSummary($row, $commonData, $rowsRef, $bwhLogsInsert, $whSummaryRows);
            }
            self::updateStatusOrderList($rowsRef);
        }

        if (count($bwhLogsInsert)) {
            $this->query->insert($bwhLogsInsert);
        }

        if (count($whSummaryRows)) {
            WarehouseInventorySummaryService::insertOrUpdateBulk(WarehouseInventorySummary::TYPE_BWH, $whSummaryRows);
        }

    }

    /**
     * @param $row
     * @param $rowsRef
     * @param $oldWarehouseCode
     * @param $oldDefectId
     * @param $whSummaryRows
     * @param bool $isUpdateShippedDate
     * @return void
     */
    private function prepareWarehouseSummaryRows($row, $rowsRef, $oldWarehouseCode, $oldDefectId, &$whSummaryRows, bool $isUpdateShippedDate = false)
    {
        $warehouseCode = $row['warehouse_code'];
        $defectId = $row['defect_id'] ?? null;
        $warehouseCodes = [];

        // TH cập nhật shipped date, tức là ship ra khỏi kho, thì trừ ở summary của kho cũ
        if ($isUpdateShippedDate) {
            if (!$oldDefectId && !$defectId && $oldWarehouseCode) {
                $warehouseCodes[$oldWarehouseCode] = true;
            }

        } // TH từ ko có warehouse sang có warehouse và defect OK
        elseif (!$oldWarehouseCode && $warehouseCode && !$oldDefectId && !$defectId) {
            $warehouseCodes[$warehouseCode] = false;
        } // TH thay đổi warehouse và defect OK
        elseif ($oldWarehouseCode != $warehouseCode && !$oldDefectId) {
            if ($oldWarehouseCode)
                $warehouseCodes[$oldWarehouseCode] = true;
            if (!$defectId)
                $warehouseCodes[$warehouseCode] = false;
        }

        foreach ($warehouseCodes as $warehouseCode => $isDecrement) {
            foreach ($rowsRef as $item) {
                $item['warehouse_type'] = WarehouseInventorySummary::TYPE_BWH;
                $item['warehouse_code'] = $warehouseCode;
                WarehouseInventorySummaryService::groupWarehouseSummaryByQuantity($item, $whSummaryRows, $isDecrement);
            }
        }
    }

    /**
     * @param $row
     * @param $commonData
     * @param $rowsRef
     * @param $bwhLogsInsert
     * @param $whSummaryRows
     * @return void
     */
    private function prepareDataForBwhInventoryAndWarehouseSummary($row, $commonData, $rowsRef, &$bwhLogsInsert, &$whSummaryRows)
    {
        $rowsRef[0]['is_parent_case'] = true;
        foreach ($rowsRef as $item) {
            $item = array_merge($row, $commonData, [
                'part_code' => $item['part_code'],
                'part_color_code' => $item['part_color_code'],
                'box_type_code' => $item['box_type_code'],
                'box_quantity' => $item['box_quantity'],
                'part_quantity' => $item['part_quantity'],
                'unit' => $item['unit'],
                'supplier_code' => $item['supplier_code'],
                'is_parent_case' => $item['is_parent_case'] ?? false
            ]);
            unset($item['row']);

            $bwhLogsInsert[] = $item;
            if (isset($row['warehouse_code']) && $row['warehouse_code'] && (!isset($row['defect_id']) || !$row['defect_id'])) {
                $item['warehouse_type'] = WarehouseInventorySummary::TYPE_BWH;
                WarehouseInventorySummaryService::groupWarehouseSummaryByQuantity($item, $whSummaryRows);
            }
        }
    }

    /**
     * @param $bwhLog
     * @return array
     */
    private function getUniqueKeysAndData($bwhLog): array
    {
        $uniqueKeys = [
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'plant_code'
        ];
        $uniqueData = array_map(function ($key) use ($bwhLog) {
            return $bwhLog[$key];
        }, $uniqueKeys);
        return [$uniqueKeys, $uniqueData];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $item
     * @param bool $force
     * @return array
     */
    public function destroy($item, bool $force = false): array
    {
        /**
         * @var BwhInventoryLog $bwhInvLog
         */
        $bwhInvLog = $this->query->where([
            'id' => $item,
            'is_parent_case' => true
        ])->firstOrFail();

        list($canDelete, $msg) = $this->canDelete($bwhInvLog);
        if (!$canDelete) {
            return [false, $msg];
        }

        DB::beginTransaction();
        try {
            list($uniqueKeys, $uniqueData) = $this->getUniqueKeysAndData($bwhInvLog);
            // delete bwh inventory logs
            BwhInventoryLog::whereInMultiple($uniqueKeys, [$uniqueData])->delete();

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }
        DB::commit();
        return [true, null];
    }

    /**
     * @param BwhInventoryLog $bwhInvLog
     * @return array
     */
    private function canDelete(BwhInventoryLog $bwhInvLog): array
    {
        $conditions = [
            'contract_code' => $bwhInvLog->contract_code,
            'invoice_code' => $bwhInvLog->invoice_code,
            'bill_of_lading_code' => $bwhInvLog->bill_of_lading_code,
            'container_code' => $bwhInvLog->container_code,
            'case_code' => $bwhInvLog->case_code,
            'plant_code' => $bwhInvLog->plant_code
        ];
        $orderRequest = BwhOrderRequest::query()
            ->where($conditions)
            ->count();

        if ($orderRequest) {
            return [false, 'Unable to delete this log. Data exists in BWH Order Request'];
        }

        return [!UpkwhInventoryLog::query()
            ->where($conditions)
            ->count(), 'Unable to delete this log. Data exists in UPK Inventory Log'];
    }

    /**
     * @param $datesValidate
     * @param array $failures
     * @param string $format
     * @param bool $throwIfErrors
     * @return void
     * @throws ValidationException
     */
    public static function validationDatePairs($datesValidate, array &$failures = [], string $format = 'd/m/Y', bool $throwIfErrors = false)
    {
        foreach ($datesValidate as $row => $dates) {
            if (!ImportHelper::__isGreaterThanOrEqualDate($dates['container_received'], $dates['devanned_date'], $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $dates, 'container_received', 'Date Container Received',
                    'Date Container Received must not be greater Date Container Devanned', $throwIfErrors);
            }

            if (!ImportHelper::__isGreaterThanOrEqualDate($dates['devanned_date'], $dates['stored_date'], $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $dates, 'devanned_date', 'Date Container Devanned',
                    'Date Container Devanned must not be greater Date Case Stored', $throwIfErrors);
            }

            if (!ImportHelper::__isGreaterThanOrEqualDate($dates['container_received'], $dates['stored_date'], $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $dates, 'container_received', 'Date Container Received',
                    'Date Container Received must not be greater Date Case Stored', $throwIfErrors);
            }

            if (!ImportHelper::__isGreaterThanOrEqualDate($dates['stored_date'], $dates['shipped_date'], $format)) {
                ImportHelper::__handleAfterDateError($failures, $row, $dates, 'stored_date', 'Date Case Stored',
                    'Date Case Stored must not be greater Date Case shipped to UPKWH', $throwIfErrors);
            }
        }

        if ($throwIfErrors && count($failures)) {
            throw ValidationException::withMessages($failures);
        }
    }

    /**
     * Update shipped of bwh inventory
     *
     * @param $attributes
     * @return array
     */
    public function shipped($attributes): array
    {
        $query = $this->model->query()
            ->where('warehouse_code', $attributes['warehouse_code'])
            ->where('plant_code', $attributes['plant_code'])
            ->where('contract_code', $attributes['contract_code'])
            ->where('bill_of_lading_code', $attributes['bill_of_lading_code'])
            ->where('container_code', $attributes['container_code'])
            ->where('case_code', $attributes['case_code'])
            ->where('invoice_code', $attributes['invoice_code'])
            ->where('requested', true);
        $inventoryLog = $query->clone()->where('is_parent_case', true)->first();

        if (!$inventoryLog) {
            return [false, 'Object not found', null];
        }
        $query->clone()->update(['shipped_date' => Carbon::now()->format('Y-m-d')]);
        return [true, null, $inventoryLog];
    }

    /**
     * @param $request
     * @return LengthAwarePaginator
     */
    public function orderRequests($request): LengthAwarePaginator
    {
        $limit = (int)($request->per_page ?? 20);

        return $this->model->query()
            ->select('bwh_order_requests.*')
            ->join('bwh_order_requests', function ($join) use ($request) {
                $join->on('bwh_order_requests.contract_code', '=', 'bwh_inventory_logs.contract_code')
                    ->on('bwh_order_requests.invoice_code', '=', 'bwh_inventory_logs.invoice_code')
                    ->on('bwh_order_requests.bill_of_lading_code', '=', 'bwh_inventory_logs.bill_of_lading_code')
                    ->on('bwh_order_requests.container_code', '=', 'bwh_inventory_logs.container_code')
                    ->on('bwh_order_requests.warehouse_location_code', '=', 'bwh_inventory_logs.warehouse_location_code')
                    ->on('bwh_order_requests.case_code', '=', 'bwh_inventory_logs.case_code')
                    ->whereNull('bwh_order_requests.deleted_at');
                if (filter_var($request->is_shipped, FILTER_VALIDATE_BOOL)) {
                    $join->whereNotNull('shipped_date');
                } else {
                    $join->whereNull('shipped_date');
                }
            })
            ->where('bwh_order_requests.warehouse_code', $request->warehouse_code)
            ->where('bwh_order_requests.plant_code', $request->plant_code)
            ->paginate($limit);
    }
}
