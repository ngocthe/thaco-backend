<?php

namespace App\Services;

use App\Models\BoxType;
use App\Models\DefectInventory;
use App\Models\PlantInventoryLog;
use App\Models\Remark;
use App\Models\WarehouseInventorySummary;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlantInventoryLogService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return PlantInventoryLog::class;
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

        if (isset($params['received_date']) && $this->checkParamFilter($params['received_date'])) {
            $this->query->where('received_date', $params['received_date']);
        }

        if (isset($params['warehouse_code']) && $this->checkParamFilter($params['warehouse_code'])) {
            $this->whereLike('warehouse_code', $params['warehouse_code']);
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
        DB::beginTransaction();
        try {
            /**
             * @var PlantInventoryLog $plantInventoryLog
             */
            list($plantInventoryLog, $quantity) = $this->createOrUpdatePlantInventoryLog($attributes);
            // update wh summary
            if (!$plantInventoryLog->defect_id) {
                $row = $plantInventoryLog->toArray();
                $row['quantity'] = $quantity;
                $row['warehouse_type'] = WarehouseInventorySummary::TYPE_PLANT_WH;
                // update or insert to plant summary
                WarehouseInventorySummaryService::insertOrUpdateBulk($row['warehouse_type'], [$row]);
            }
            // save defect status by box
            DefectInventory::query()
                ->updateOrCreate([
                    'modelable_type' => PlantInventoryLog::class,
                    'modelable_id' => $plantInventoryLog->id,
                    'box_id' => $plantInventoryLog->received_box_quantity
                ], [
                    'defect_id' => $attributes['defect_id'] ?? null
                ]);

            // check have any box defect?
            $hasDefect = DefectInventory::query()->where([
                'modelable_type' => PlantInventoryLog::class,
                'modelable_id' => $plantInventoryLog->id
            ])->whereNotNull('defect_id')
                ->count();
            if ($hasDefect) {
                $plantInventoryLog->defect_id = 'W';
                $plantInventoryLog->save();
            }

            DB::commit();
            return [$plantInventoryLog, ''];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, $exception->getMessage()];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|int $parent
     * @param array $attributes
     * @param bool $hasRemark
     * @return Model
     *
     * @throws Exception
     */
    public function update($parent, array $attributes, bool $hasRemark = true): Model
    {
        $plantLog = $this->query->findOrFail($parent);
        $plantLog->fill($attributes);
        $plantLog->save();
        $this->createRemark($plantLog);
        return $plantLog;
    }

    /**
     * @param $attributes
     * @return array
     */
    private function createOrUpdatePlantInventoryLog($attributes): array
    {
        $boxType = BoxType::query()
            ->where([
                'code' => $attributes['box_type_code'],
                'part_code' => $attributes['part_code'],
                'plant_code' => $attributes['plant_code'],
            ])->first();
        $attributes['unit'] = $boxType->unit;
        $quantity = $boxType->quantity;

        /**
         * @var PlantInventoryLog $plantInventoryLog
         */

        $plantInventoryLog = $this->query
            ->where([
                'part_code' => $attributes['part_code'],
                'part_color_code' => $attributes['part_color_code'],
                'box_type_code' => $attributes['box_type_code'],
                'received_date' => $attributes['received_date'],
                'warehouse_code' => $attributes['warehouse_code'],
                'plant_code' => $attributes['plant_code']
            ])
            ->first();

        if (!$plantInventoryLog) {
            $attributes['quantity'] = $quantity;
            $attributes['received_box_quantity'] = 1;
            $plantInventoryLog = $this->query->create($attributes);
        } else {
            $plantInventoryLog->quantity += $quantity;
            $plantInventoryLog->received_box_quantity += 1;
            if (isset($attributes['defect_id'])) {
                $plantInventoryLog->defect_id = $attributes['defect_id'];
            }
            $plantInventoryLog->save();
        }

        $this->createRemark($plantInventoryLog);

        return [$plantInventoryLog, $quantity];
    }

    /**
     * @param int $plantLogId
     * @param array $attributes
     * @return array
     *
     * @throws Exception
     */
    public function defects(int $plantLogId, array $attributes): array
    {
        /**
         * @var PlantInventoryLog $plantLog
         */
        $plantLog = $this->query->findOrFail($plantLogId);
        $plantLog->defect_id = null;
        $boxList = $attributes['box_list'] ?? [];
        $partQuantityInBox = $plantLog->quantity / $plantLog->received_box_quantity;
        $boxErrors = $this->validateBoxListDefect($boxList, $partQuantityInBox);
//        dd($boxErrors);
        if (count($boxErrors)) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => false,
                    'message' => 'Defect data submitted is incorrect',
                    'data' => $boxErrors
                ], 400));
        } else {
            DB::beginTransaction();
            try {
                $plantLog->save();
                $this->createRemark($plantLog);
                foreach ($boxList as $box) {
                    if ($box['id'] <= $plantLog->received_box_quantity) {
                        $defectId = $box['defect_id'];
                        $this->updateWarehouseSummaryByDefectStatus($plantLog, $box['id'], $box['remark'] ?? null,
                            $defectId, $box['part_defect_quantity']);
                        if ($defectId) {
                            $plantLog->defect_id = 'W';
                        }
                    }
                }

                if ($plantLog->save()) {
                    DB::commit();
                    return [$plantLog, null, null];
                } else {
                    return [false, 'Unable to update data', null];
                }
            } catch (Exception $exception) {
                DB::rollBack();
                Log::error($exception);
                return [false, $exception->getMessage(), null];
            }
        }
    }

    /**
     * @param $boxList
     * @param $partQuantityInBox
     * @return array
     */
    private function validateBoxListDefect($boxList, $partQuantityInBox): array
    {
        $boxErrors = [];
        foreach ($boxList as $index => $box) {
            if (isset($box['defect_id']) && $box['defect_id']) {
                if (!$box['part_defect_quantity']) {
                    $boxErrors['box_list.'.$index.'.part_defect_quantity'] = [
                        'code' => '10069',
                        'message' => 'The box_list.'.$index.'.part_defect_quantity must be a positive number'
                    ];
                } elseif ($box['part_defect_quantity'] > $partQuantityInBox) {
                    $boxErrors['box_list.'.$index.'.part_defect_quantity'] = [
                        'code' => '10069',
                        'message' => 'The box_list.'.$index.'.part_defect_quantity must not be greater than ' . $partQuantityInBox
                    ];
                }
            }
        }
        return $boxErrors;
    }

    /**
     * @param PlantInventoryLog $plantLog
     * @param $boxId
     * @param $remark
     * @param $boxDefectId
     * @param null $partDefectQuantity
     * @return void
     */
    private function updateWarehouseSummaryByDefectStatus(
        PlantInventoryLog $plantLog,
                          $boxId,
                          $remark,
                          $boxDefectId,
                          $partDefectQuantity = null
    )
    {
        $defectInv = DefectInventory::query()
            ->firstOrNew([
                'modelable_type' => PlantInventoryLog::class,
                'modelable_id' => $plantLog->id,
                'box_id' => $boxId
            ]);

        $oldBoxDefectId = $defectInv->getOriginal('defect_id');
        $oldPartDefectQuantity = $defectInv->getOriginal('part_defect_quantity');
        $defectInv->defect_id = $boxDefectId;
        $defectInv->part_defect_quantity = $partDefectQuantity;
        $defectInv->save();

        if ($boxDefectId && $remark) {
            $this->updateOrCreateDefectRemark($defectInv->id, $remark);
        }
// comment code, ko update summary theo task THAC-1154
//        if ($boxDefectId && !$oldBoxDefectId) {
//            // Đổi từ OK sang defect
//            WarehouseInventorySummaryService::decrementQuantityOfPart(WarehouseInventorySummary::TYPE_PLANT_WH,
//                $plantLog->plant_code,
//                $plantLog->warehouse_code, $plantLog->part_code, $plantLog->part_color_code, $defectInv->part_defect_quantity);
//        } elseif (!$boxDefectId && $oldBoxDefectId) {
//            $row = $plantLog->toArray();
//            $row['quantity'] = $oldPartDefectQuantity;
//            $row['warehouse_type'] = WarehouseInventorySummary::TYPE_PLANT_WH;
//            WarehouseInventorySummaryService::insertOrUpdateBulk($row['warehouse_type'], [$row]);
//        } elseif ($boxDefectId && $oldPartDefectQuantity != $defectInv->part_defect_quantity) {
//            if ($oldPartDefectQuantity) {
//                $decrementQuantity = ($defectInv->part_defect_quantity ?: 0) - $oldPartDefectQuantity;
//                WarehouseInventorySummaryService::decrementQuantityOfPart(WarehouseInventorySummary::TYPE_PLANT_WH,
//                    $plantLog->plant_code,
//                    $plantLog->warehouse_code, $plantLog->part_code, $plantLog->part_color_code, $decrementQuantity);
//            } else {
//                $row = $plantLog->toArray();
//                $row['quantity'] = $plantLog->quantity / $plantLog->received_box_quantity - $defectInv->part_defect_quantity;
//                $row['warehouse_type'] = WarehouseInventorySummary::TYPE_PLANT_WH;
//                WarehouseInventorySummaryService::insertOrUpdateBulk($row['warehouse_type'], [$row]);
//            }
//        }
    }

    /**
     * @param $defectInvId
     * @param $remarkContent
     * @return void
     */
    private function updateOrCreateDefectRemark($defectInvId, $remarkContent)
    {
        Remark::query()->updateOrCreate(
            [
                'modelable_type' => DefectInventory::class,
                'modelable_id' => $defectInvId
            ],
            [
                'content' => $remarkContent ?: ''
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $item
     * @param bool $force
     * @return bool
     *
     */
    public function destroy($item, bool $force = false): bool
    {
        /**
         * @var PlantInventoryLog $plantLog
         */
        $plantLog = $this->query->findOrFail($item);
        $updateSummary = request()->get('update_summary', false);
        if ($updateSummary == 'true' || $updateSummary == 1) {
            $updateSummary = true;
        }
        DB::beginTransaction();
        try {
            if ($updateSummary && $plantLog->warehouse_code) {
                $this->createWarehouseAdjustment($plantLog);
            }
            // delete bwh inventory logs
            $plantLog->delete();

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }
        DB::commit();
        return true;
    }

    /**
     * @param PlantInventoryLog $plantLog
     * @return void
     */
    private function createWarehouseAdjustment(PlantInventoryLog $plantLog)
    {
        $uniqueKeys = [
            'warehouse_code',
            'part_code',
            'part_color_code',
            'plant_code'
        ];
        $warehouseCodes = [$plantLog->warehouse_code];
        $data = [
            'warehouse_code' => $plantLog->warehouse_code,
            'part_code' => $plantLog->part_code,
            'part_color_code' => $plantLog->part_color_code,
            'plant_code' => $plantLog->plant_code
        ];
        $uniqueData = [$data];
        // get adjustment_quantity
        $numberBoxDefect = DefectInventory::query()
            ->where([
                'modelable_type' => PlantInventoryLog::class,
                'modelable_id' => $plantLog->id,
            ])
            ->whereNotNull('defect_id')
            ->count();
        $partQuantity = $plantLog->quantity / $plantLog->received_box_quantity;
        $data['adjustment_quantity'] = round(-1 * ($plantLog->received_box_quantity - $numberBoxDefect) * $partQuantity);
        $data['row'] = '';
        (new WarehouseSummaryAdjustmentService())->insertBulk([$data], $uniqueKeys, $uniqueData, $warehouseCodes, true);
    }
}
