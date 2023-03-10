<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\LogicalInventory;
use App\Models\LogicalInventoryMscAdjustment;
use App\Models\PartColor;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Model;

class LogicalInventoryMscAdjustmentService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return LogicalInventoryMscAdjustment::class;
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

        if (isset($params['msc_code']) && $this->checkParamFilter($params['msc_code'])) {
            $this->whereLike('msc_code', $params['msc_code']);
        }

        if (isset($params['vehicle_color_code']) && $this->checkParamFilter($params['vehicle_color_code'])) {
            $this->whereLike('vehicle_color_code', $params['vehicle_color_code']);
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
        list($partAndPartColorData, $partAndPartColorQuantity) = $this->getPartAndPartColor($attributes);
        if (!count($partAndPartColorData)) {
            return [false, 'No suitable data found.'];
        }
        $this->insertIntoLogicalInventoryBulk($partAndPartColorData, $partAndPartColorQuantity);

        $parent = $this->query->create($attributes);
        $this->createRemark($parent);
        return [$parent, null];
    }

    /**
     * @param array $data
     * @return array
     */
    private function getPartAndPartColor(array $data): array
    {
        $boms = Bom::query()
            ->select(['part_code', 'part_color_code', 'quantity'])
            ->where([
                'msc_code' => $data['msc_code'],
                'plant_code' => $data['plant_code']
            ])
            ->get()
            ->toArray();
        $partAndPartColorData = [];
        $partAndPartColorQuantity = [];
        foreach ($boms as $bom) {
            $partAndPartColorData[] = [
                'production_date' => $data['production_date'],
                'part_code' => $bom['part_code'],
                'part_color_code' => $this->getPartColorCode($bom, $data),
                'plant_code' => $data['plant_code']
            ];
            $key = $bom['part_code'] . '-' . $bom['part_color_code'] . '-' . $data['plant_code'] . '-' . $data['production_date'];
            $partAndPartColorQuantity[$key] = $bom['quantity'] * $data['adjustment_quantity'];
        }

        return [$partAndPartColorData, $partAndPartColorQuantity];
    }

    /**
     * @param $logicalInventory
     * @param $data
     * @param $loggedId
     * @param $logicalRows
     * @return array
     */
    private function handleData(
        $logicalInventory,
        $data,
        $loggedId,
        $logicalRows
    ): array {
        $adjustment_quantity = intval($data['adjustment_quantity']);
        if ($logicalInventory) {
            $logicalInventory->quantity += $adjustment_quantity;
            $logicalInventory = $logicalInventory->toArray();
            unset($logicalInventory['id']);
            unset($logicalInventory['created_at']);
            unset($logicalInventory['updated_at']);
            unset($logicalInventory['deleted_at']);
            $logicalRows[] = $logicalInventory;
        } else {

            $logicalRows[] = [
                'production_date' => $data['production_date'],
                'plant_code' => $data['plant_code'],
                'part_code' => $data['part_code'],
                'part_color_code' => $data['part_color_code'],
                'quantity' => $adjustment_quantity,
                'created_by' => $loggedId,
                'updated_by' => $loggedId
            ];
        }

        return $logicalRows;
    }

    /**
     * @param $bom
     * @param $data
     * @return HigherOrderBuilderProxy|mixed|string
     */
    public function getPartColorCode($bom, $data)
    {
        if ($bom['part_color_code'] == 'XX') {
            $partColor = PartColor::query()
                ->select('code')
                ->where([
                    'part_code' => $bom['part_code'],
                    'vehicle_color_code' => $data['vehicle_color_code']
                ])->first();
            return $partColor ? $partColor->code : 'XX';
        } else {
            return $bom['part_color_code'];
        }
    }

    /**
     * @param $partAndPartColorData
     * @param $partAndPartColorQuantity
     * @return void
     */
    public function insertIntoLogicalInventoryBulk($partAndPartColorData, $partAndPartColorQuantity)
    {
        $logicalInventories = LogicalInventory::whereInMultiple(
            ['production_date', 'part_code', 'part_color_code', 'plant_code'],
            $partAndPartColorData
        )->get();
        $rowsExists = [];
        $uniqueKeys = [
            'part_code',
            'part_color_code',
            'plant_code',
            'production_date'
        ];
        foreach ($logicalInventories as $log) {
            $key = $this->convertDataToKey($uniqueKeys, $log);
            $rowsExists[$key] = $log;
        }
        $logicalRows = [];
        $loggedId = auth()->id();
        foreach ($partAndPartColorData as $data) {
            $keyByData = $this->convertDataToKey($uniqueKeys, $data);
            $logicalInventory = $rowsExists[$keyByData] ?? null;
            $data['adjustment_quantity'] = $partAndPartColorQuantity[$keyByData];
            $logicalRows = $this->handleData($logicalInventory, $data, $loggedId, $logicalRows);
        }

        if (count($logicalRows)) {
            $logicalRows = array_chunk($logicalRows, 1000);
            foreach ($logicalRows as $rows) {
                LogicalInventory::query()
                    ->upsert(
                        $rows,
                        ['production_date', 'part_code', 'part_color_code', 'plant_code'],
                        ['quantity', 'created_by', 'updated_by']
                    );
            }
        }
    }
}
