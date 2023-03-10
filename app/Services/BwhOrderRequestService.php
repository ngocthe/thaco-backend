<?php

namespace App\Services;

use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\Remark;
use App\Models\UpkwhInventoryLog;
use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BwhOrderRequestService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return BwhOrderRequest::class;
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
        $searchLike = $params['search_like'] ?? true;
        if (isset($params['case_code']) && $this->checkParamFilter($params['case_code'])) {
            $this->whereLike('case_code', $params['case_code']);
        }
        $this->addFilterPartAndPartColor($params, $searchLike);
        $this->addFilterPlantCode($params, $searchLike);
        $this->addFilterWarehouseCode($params, $searchLike);
    }

    /**
     * @param array $attributes
     * @param bool $hasRemark
     * @return int
     * @throws Exception
     */
    public function store(array $attributes, bool $hasRemark = true): int
    {
        $bwhOrderRequestRows = [];
        $partQuantityData = [];
        $caseUsed = [];
        $this->createBwhOrderRequests(
            $attributes['part_code'],
            $attributes['part_color_code'],
            $attributes['plant_code'],
            $attributes['box_quantity'],
            $bwhOrderRequestRows,
            $partQuantityData,
            $caseUsed,
            Carbon::now()->toDateTimeString(),
            null,
            auth()->id(),
        );

        if (empty($bwhOrderRequestRows)) {
            return 0;
        }

        if (count($caseUsed)) {
            BwhInventoryLog::whereInMultiple(
                ['contract_code', 'invoice_code', 'bill_of_lading_code', 'container_code', 'case_code', 'plant_code'],
                array_values($caseUsed)
            )
                ->update(['requested' => true]);
        }

        return $this->query->insertOrIgnore($bwhOrderRequestRows);
    }

    /**
     * @param $partCode
     * @param $partColorCode
     * @param $plantCode
     * @param $boxQuantityShortage
     * @param $bwhOrderRequestRows
     * @param $partQuantityData
     * @param $caseUsed
     * @param $dateTime
     * @param null $boxTypeCode
     * @param null $adminId
     * @return void
     */
    public function createBwhOrderRequests($partCode, $partColorCode, $plantCode, $boxQuantityShortage,
                                           &$bwhOrderRequestRows, &$partQuantityData, &$caseUsed,
                                           $dateTime, $boxTypeCode = null, $adminId = null)
    {
        $bwhInvLogs = $this->getBwhInventoryLogs($partCode, $partColorCode, $plantCode, $boxTypeCode, $caseUsed);
        Log::alert('BWH Inventory Logs: ' . count($bwhInvLogs));
        $totalBoxQuantity = 0;
        $date = Carbon::now()->toDateString();
        foreach ($bwhInvLogs as $log) {
            $bwhOrderRequest = [
                'order_number' => $date . '-' . substr($log['case_code'], -2),
                'contract_code' => $log['contract_code'],
                'invoice_code' => $log['invoice_code'],
                'bill_of_lading_code' => $log['bill_of_lading_code'],
                'container_code' => $log['container_code'],
                'case_code' => $log['case_code'],
                'supplier_code' => $log['supplier_code'],
                'part_code' => $partCode,
                'part_color_code' => $partColorCode,
                'box_type_code' => $boxTypeCode,
                'box_quantity' => $log['box_quantity'],
                'part_quantity' => $log['part_quantity'],
                'warehouse_code' => $log['warehouse_code'],
                'warehouse_location_code' => $log['warehouse_location_code'],
                'plant_code' => $log['plant_code'],
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ];
            $bwhOrderRequestRows[] = $bwhOrderRequest;
            $totalBoxQuantity += $log['box_quantity'];
            $this->markUsedData($log, $partQuantityData, $caseUsed);

            Log::alert('New Bwh Order Request', [
                'order_number' => $bwhOrderRequest['order_number'],
                'part_code' => $partCode,
                'part_color_code' => $partColorCode,
                'box_type_code' => $boxTypeCode,
                'box_quantity' => $log['box_quantity'],
                'remain_box_quantity' => $boxQuantityShortage - $totalBoxQuantity
            ]);
            Log::alert('Part Quantity Data: ', $partQuantityData);
            Log::alert('Case Used: ', $caseUsed);
            if ($totalBoxQuantity >= $boxQuantityShortage) break;
        }
    }

    /**
     * @param $partCode
     * @param $partColorCode
     * @param $plantCode
     * @param $boxTypeCode
     * @param array $caseUsed
     * @return array
     */
    protected function getBwhInventoryLogs($partCode, $partColorCode, $plantCode, $boxTypeCode, array $caseUsed = []): array
    {
        if (count($caseUsed)) {
            $query = BwhInventoryLog::whereInMultiple(
                ['contract_code', 'invoice_code', 'bill_of_lading_code', 'container_code', 'case_code', 'plant_code'],
                array_values($caseUsed),
                [],
                true
            );
        } else {
            $query = BwhInventoryLog::query();
        }
        $query->select('contract_code', 'invoice_code', 'bill_of_lading_code', 'container_code',
            'case_code', 'part_code', 'part_color_code', 'box_type_code', 'box_quantity', 'part_quantity',
            'warehouse_code', 'warehouse_location_code', 'plant_code', 'supplier_code'
        )
            ->where([
                'part_code' => $partCode,
                'part_color_code' => $partColorCode,
                'plant_code' => $plantCode,
                'requested' => false
            ]);
        if ($boxTypeCode) {
            $query->where('box_type_code', $boxTypeCode);
        }

        return $query->whereNull('shipped_date')
            ->whereNull('defect_id')
            ->orderBy('stored_date')
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    /**
     * @param $log
     * @param $partQuantityData
     * @param $caseUsed
     * @return void
     */
    protected function markUsedData($log, &$partQuantityData, &$caseUsed)
    {
        $caseKeys = ['contract_code', 'invoice_code', 'bill_of_lading_code', 'container_code', 'case_code', 'plant_code'];
        $caseUsedKey = $this->convertDataToKey($caseKeys, $log);
        $caseUsed[$caseUsedKey] = array_map(function ($k) use ($log) {
            return $log[$k];
        }, $caseKeys);
        $rows = BwhInventoryLog::query()
            ->select('part_code', 'part_color_code', 'box_type_code', 'box_quantity', 'plant_code')
            ->where([
                'contract_code' => $log['contract_code'],
                'invoice_code' => $log['invoice_code'],
                'bill_of_lading_code' => $log['bill_of_lading_code'],
                'container_code' => $log['container_code'],
                'case_code' => $log['case_code'],
            ])->get()->toArray();
        foreach ($rows as $row) {
            $key = $this->convertDataToKey(['part_code', 'part_color_code', 'box_type_code', 'plant_code'], $row);
            if (!isset($partQuantityData[$key])) $partQuantityData[$key] = 0;
            $partQuantityData[$key] += $row['box_quantity'];
        }
    }

    /**
     * @param $id
     * @param $dataConfirm
     * @return array
     */
    public function confirmBwhOrderRequest($id, $dataConfirm): array
    {
        /**
         * @var BwhOrderRequest $order
         */
        $order = $this->query->find($id);
        if (!$order) {
            return [false, 'The order request is not found or is deleted'];
        }
        $exists = Warehouse::query()
            ->where([
                'code' => $dataConfirm['warehouse_code'],
                'plant_code' => $order->plant_code
            ])
            ->exists();
        if (!$exists) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => false,
                    'message' => 'Warehouse Code, Plant Code are not linked together',
                    'data' => [
                        'warehouse_code' => [
                            'code' => '10032',
                            'message' => 'Warehouse Code, Plant Code are not linked together'
                        ]
                    ]
                ], 400));
        }

        /**
         * @var BwhInventoryLog $bwhLog
         */
        $caseData = [
            'contract_code' => $order->contract_code,
            'invoice_code' => $order->invoice_code,
            'bill_of_lading_code' => $order->bill_of_lading_code,
            'container_code' => $order->container_code,
            'case_code' => $order->case_code,
            'plant_code' => $order->plant_code
        ];
        $bwhInvLog = BwhInventoryLog::query()
            ->where($caseData)
            ->where('requested', true)
            ->first();
        if (!$bwhInvLog || $bwhInvLog->defect_id) {
            return [false, 'Unable to confirm. Case Pallet is not found or has defect'];
        }

        $bwhInvLogs = BwhInventoryLog::query()
            ->where([
                'contract_code' => $order->contract_code,
                'invoice_code' => $order->invoice_code,
                'bill_of_lading_code' => $order->bill_of_lading_code,
                'container_code' => $order->container_code,
                'case_code' => $order->case_code,
                'plant_code' => $order->plant_code
            ])
            ->get()
            ->toArray();

        $loggedId = auth()->id();
        $currentTime = Carbon::now()->toDateTimeString();
        $caseData = array_merge($caseData, [
            'created_by' => $loggedId,
            'updated_by' => $loggedId,
            'created_at' => $currentTime,
            'updated_at' => $currentTime
        ]);
        list($upkwhLogsInsert, $whSummaryRows) = $this->prepareDataForUpkwhInventoryAndWarehouseSummary($dataConfirm, $caseData, $bwhInvLogs);

        DB::beginTransaction();
        try {
            if (count($upkwhLogsInsert)) {
                UpkwhInventoryLog::query()->insert($upkwhLogsInsert);
                if (isset($dataConfirm['remark'])) {
                    $this->createRemarksForUpkwh($caseData, $dataConfirm['remark']);
                }
            }
            if (count($whSummaryRows)) {
                WarehouseInventorySummaryService::insertOrUpdateBulk(WarehouseInventorySummary::TYPE_UPKWH, $whSummaryRows);
            }
            $order->delete();
            DB::commit();
            return [$order, null];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, $exception->getMessage()];
        }
    }

    /**
     * @param $caseData
     * @param $remark
     * @return void
     */
    private function createRemarksForUpkwh($caseData, $remark)
    {
        $keys = array_keys($caseData);
        $values = [array_values($caseData)];
        $upkwhIds = UpkwhInventoryLog::whereInMultiple($keys, $values)
            ->select('id')->pluck('id')->toArray();
        $remarks = [];
        $loggedId = auth()->id();
        $currentTime = Carbon::now()->toDateTimeString();
        foreach ($upkwhIds as $id) {
            $remarks[] = [
                'modelable_type' => UpkwhInventoryLog::class,
                'modelable_id' => $id,
                'content' => $remark,
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
        }
        Remark::query()->insert($remarks);
    }

    /**
     * @param $dataConfirm
     * @param $caseData
     * @param $rowsRef
     * @return array[]
     */
    private function prepareDataForUpkwhInventoryAndWarehouseSummary($dataConfirm, $caseData, $rowsRef): array
    {
        $whSummaryRows = [];
        $upkwhLogsInsert = [];
        $upkwhData = array_merge($caseData, [
            'received_date' => $dataConfirm['received_date'] ?? null,
            'shelf_location_code' => $dataConfirm['shelf_location_code'] ?? null,
            'warehouse_code' => $dataConfirm['warehouse_code'] ?? null,
            'defect_id' => $dataConfirm['defect_id'] ?? null
        ]);

        foreach ($rowsRef as $item) {
            $item = array_merge($upkwhData, [
                'part_code' => $item['part_code'],
                'part_color_code' => $item['part_color_code'],
                'box_type_code' => $item['box_type_code'],
                'box_quantity' => $item['box_quantity'],
                'part_quantity' => $item['part_quantity'],
                'unit' => $item['unit'],
                'supplier_code' => $item['supplier_code']
            ]);

            $upkwhLogsInsert[] = $item;
            if (isset($item['warehouse_code']) && $item['warehouse_code'] && (!isset($item['defect_id']) || !$item['defect_id'])) {
                $item['warehouse_type'] = WarehouseInventorySummary::TYPE_UPKWH;
                WarehouseInventorySummaryService::groupWarehouseSummaryByQuantity($item, $whSummaryRows);
            }
        }

        return [$upkwhLogsInsert, $whSummaryRows];
    }

    /**
     * @param array $partData
     * @return void
     */
    public function runBathCreateBwhOrderRequest(array $partData = [])
    {
        Log::alert('Start run Bath Create BWH Order Request.');
        $orderPointControls = $this->getOrderPointControls($partData);
        Log::alert('Total Order point controls: ' . count($orderPointControls));
        if (count($orderPointControls)) {
            $bwhOrderRequestRows = [];
            $partQuantityData = [];
            $caseUsed = [];
            $dateTime = Carbon::now()->toDateTimeString();
            foreach ($orderPointControls as $orderPointControl) {
                Log::alert('Create BWH Order request for: ', $orderPointControl);
                $key = $this->convertDataToKey(['part_code', 'part_color_code', 'box_type_code', 'plant_code'], $orderPointControl);
                if (isset($partQuantityData[$key])) {
                    if ($orderPointControl['ordering_lot'] <= $partQuantityData[$key]) {
                        $partQuantityData[$key] -= $orderPointControl['ordering_lot'];
                        Log::alert('Part already has enough in the cases!!!');
                        continue;
                    } else {
                        $boxQuantityShortage = $orderPointControl['ordering_lot'] - $partQuantityData[$key];
                    }
                } else {
                    $boxQuantityShortage = $orderPointControl['ordering_lot'];
                }
                Log::alert('Box Quantity Shortage: ' . $boxQuantityShortage);
                $this->createBwhOrderRequests(
                    $orderPointControl['part_code'],
                    $orderPointControl['part_color_code'],
                    $orderPointControl['plant_code'],
                    $boxQuantityShortage,
                    $bwhOrderRequestRows,
                    $partQuantityData,
                    $caseUsed,
                    $dateTime,
                    $orderPointControl['box_type_code']
                );
            }

            if (count($caseUsed)) {
                BwhInventoryLog::whereInMultiple(
                    ['contract_code', 'invoice_code', 'bill_of_lading_code', 'container_code', 'case_code', 'plant_code'],
                    array_values($caseUsed)
                )
                    ->update(['requested' => true]);
            }
            Log::alert('Total Bwh Order Request Created: ' . count($bwhOrderRequestRows));
            $bwhOrderRequestRows = array_chunk($bwhOrderRequestRows, 500);
            foreach ($bwhOrderRequestRows as $data) {
                BwhOrderRequest::query()->insertOrIgnore($data);
            }
        }
    }

    /**
     * @param array $partData
     * @return array
     */
    private function getOrderPointControls(array $partData = []): array
    {
        // sử dụng QueryBuilder thay cho Eloquent để tăng performance
        $query = DB::table('order_point_controls')
            ->select(
                'order_point_controls.part_code',
                'order_point_controls.part_color_code',
                'order_point_controls.box_type_code',
                'order_point_controls.standard_stock',
                'order_point_controls.ordering_lot',
                'order_point_controls.plant_code',
                'warehouse_inventory_summaries.warehouse_code',
                DB::raw('CEILING(COALESCE(warehouse_inventory_summaries.quantity, 0) / box_types.quantity) as box_quantity')
            )
            ->leftJoin('warehouse_inventory_summaries', function ($join) {
                $join->on('order_point_controls.part_code', '=', 'warehouse_inventory_summaries.part_code')
                    ->on('order_point_controls.part_color_code', '=', 'warehouse_inventory_summaries.part_color_code')
                    ->on('order_point_controls.plant_code', '=', 'warehouse_inventory_summaries.plant_code')
                    ->where('warehouse_type', '=', WarehouseInventorySummary::TYPE_UPKWH);
            })
            ->leftJoin('box_types', function ($join) {
                $join->on('order_point_controls.part_code', '=', 'box_types.part_code')
                    ->on('order_point_controls.plant_code', '=', 'box_types.plant_code')
                    ->on('order_point_controls.box_type_code', '=', 'box_types.code');
            })
            ->whereRaw(DB::raw('CEILING(COALESCE(warehouse_inventory_summaries.quantity, 0) / box_types.quantity) < standard_stock'))
            ->whereNull('order_point_controls.deleted_at');

        if (count($partData)) {
            $values = array_map(function (array $value) {
                return "('" . implode("', '", $value) . "')";
            }, $partData);
            $query->whereRaw(
                '(warehouse_inventory_summaries.part_code, warehouse_inventory_summaries.part_color_code, warehouse_inventory_summaries.plant_code) in (' . implode(', ', $values) . ')'
            );
        }
        $keys = ['part_code', 'part_color_code', 'box_type_code', 'plant_code'];
        $rows = $query->orderBy('order_point_controls.id')->get();
        $data = [];
        foreach ($rows as $row) {
            $row = (array)$row;
            $key = $this->convertDataToKey($keys, $row);
            $data[$key] = $row;
        }
        return $data;
    }

    /**
     * @param $request
     * @return LengthAwarePaginator
     */
    public function listOrderRequests($request): LengthAwarePaginator
    {
        $limit = (int)($request->per_page ?? 20);

        return $this->model->query()
            ->join('bwh_inventory_logs', function ($join) use ($request) {
                $join->on('bwh_order_requests.contract_code', '=', 'bwh_inventory_logs.contract_code')
                    ->on('bwh_order_requests.invoice_code', '=', 'bwh_inventory_logs.invoice_code')
                    ->on('bwh_order_requests.bill_of_lading_code', '=', 'bwh_inventory_logs.bill_of_lading_code')
                    ->on('bwh_order_requests.container_code', '=', 'bwh_inventory_logs.container_code')
                    ->on('bwh_order_requests.case_code', '=', 'bwh_inventory_logs.case_code')
                    ->where('bwh_order_requests.warehouse_code', $request->warehouse_code)
                    ->where('bwh_order_requests.plant_code', $request->plant_code);
                if (filter_var($request->is_shipped, FILTER_VALIDATE_BOOL)) {
                    $join->whereNotNull('shipped_date');
                } else {
                    $join->whereNull('shipped_date');
                }
            })->paginate($limit);
    }
}
