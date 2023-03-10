<?php

namespace App\Services;

use App\Models\LogicalInventory;
use App\Models\LogicalInventoryPartAdjustment;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;

class LogicalInventoryPartAdjustmentService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return LogicalInventoryPartAdjustment::class;
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
        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);
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
        $currentTime = Carbon::now();
        $logicalInventory = LogicalInventory::query()
            ->where([
                'production_date' => $attributes['adjustment_date'],
                'part_code' => $attributes['part_code'],
                'part_color_code' => $attributes['part_color_code'],
                'plant_code' => $attributes['plant_code'],
            ])
            ->first();

        list($old_quantity, $new_quantity, $logicalRows) = $this->handleData($logicalInventory, $attributes,
            $currentTime->toDateString(),
            auth()->id(), $currentTime->toDateTimeString(), []);

        $attributes['old_quantity'] = $old_quantity;
        $attributes['new_quantity'] = $new_quantity;

        if (count($logicalRows)) {
            LogicalInventory::query()->upsert(
                $logicalRows,
                ['production_date', 'part_code', 'part_color_code', 'plant_code'],
                ['quantity', 'created_by', 'updated_by']
            );
        }

        $parent = $this->query->create($attributes);
        $this->createRemark($parent);
        return $parent->push() ? $parent : false;
    }

    /**
     * @param $dataImport
     * @param $uniqueKeys
     * @param $uniqueData
     * @return void
     */
    public function insertBulk($dataImport, $uniqueKeys, $uniqueData)
    {
        $logicalRows = [];
        $rowsInsert = [];
        $loggedId = auth()->id();
        $currentTime = Carbon::now();
        $productionDate = $currentTime->toDateString();
        $dateTime = $currentTime->toDateTimeString();

        $rowsExists = [];
        $logicalInventories = LogicalInventory::whereInMultiple($uniqueKeys, $uniqueData)
            ->where('production_date', $productionDate)
            ->get();
        foreach ($logicalInventories as $log) {
            $key = $this->convertDataToKey($uniqueKeys, $log);
            $rowsExists[$key] = $log;
        }

        foreach ($dataImport as $data) {
            unset($data['row']);
            $keyByData = $this->convertDataToKey($uniqueKeys, $data);
            $logicalInventory = $rowsExists[$keyByData] ?? null;
            list($old_quantity, $new_quantity, $logicalRows) = $this->handleData($logicalInventory, $data,
                $productionDate, $loggedId, $dateTime, $logicalRows);
            $rowsInsert[] = array_merge($data, [
                'adjustment_date' => $productionDate,
                'old_quantity' => $old_quantity,
                'new_quantity' => $new_quantity,
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ]);
        }

        if (count($logicalRows)) {
            $logicalRows = array_chunk($logicalRows, 1000);
            foreach ($logicalRows as $data) {
                LogicalInventory::query()->upsert(
                    $data,
                    ['production_date', 'part_code', 'part_color_code', 'plant_code'],
                    ['quantity', 'created_by', 'updated_by']
                );
            }
        }

        if (count($rowsInsert)) {
            $this->query->insert($rowsInsert);
        }
    }

    /**
     * @param $logicalInventory
     * @param $data
     * @param $productionDate
     * @param $loggedId
     * @param $currentTime
     * @param $logicalRows
     * @return array
     */
    private function handleData(
        $logicalInventory,
        $data,
        $productionDate,
        $loggedId,
        $currentTime,
        $logicalRows
    ): array {
        $adjustment_quantity = intval($data['adjustment_quantity']);
        if ($logicalInventory) {
            $data['old_quantity'] = $logicalInventory->quantity;
            $data['new_quantity'] = $logicalInventory->quantity + $adjustment_quantity;
            $logicalInventory->quantity = $data['new_quantity'];

            $logicalInventory = $logicalInventory->toArray();
            unset($logicalInventory['id']);
            unset($logicalInventory['created_at']);
            unset($logicalInventory['updated_at']);
            unset($logicalInventory['deleted_at']);
            $logicalRows[] = $logicalInventory;
        } else {
            $data['old_quantity'] = 0;
            $data['new_quantity'] = $adjustment_quantity;

            $logicalRows[] = [
                'production_date' => $productionDate,
                'plant_code' => $data['plant_code'],
                'part_code' => $data['part_code'],
                'part_color_code' => $data['part_color_code'],
                'quantity' => $adjustment_quantity,
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
        }

        return [$data['old_quantity'], $data['new_quantity'], $logicalRows];
    }
}
