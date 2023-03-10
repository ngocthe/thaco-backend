<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class WarehouseInventorySummaryService extends BaseService
{
    const UNIQUE_KEYS = [
        'part_code',
        'part_color_code',
        'warehouse_code',
        'warehouse_type',
        'plant_code'
    ];
    /**
     * @return string
     */
    public function model(): string
    {
        return WarehouseInventorySummary::class;
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

        if (isset($params['warehouse_code']) && $this->checkParamFilter($params['warehouse_code'])) {
            $this->whereLike('warehouse_code', $params['warehouse_code']);
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterUpdatedAt($params);

    }

    /**
     * @param $params
     * @param array $relations
     * @param bool $withTrashed
     * @return LengthAwarePaginator
     */
    public function paginate($params = null, array $relations = [], bool $withTrashed = false): LengthAwarePaginator
    {
        $params = $params ?: request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        $this->buildBasicQuery($params, $relations, $withTrashed);
        $this->addFilterPlantCode($params);
        return $this->query->latest('id')->paginate($limit);
    }

    /**
     * @param bool $isPaginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function filterGroupByPart(bool $isPaginate = true)
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);
        $this->query->selectRaw("
            part_code, part_color_code, unit,
            GROUP_CONCAT(quantity SEPARATOR ',') as quantity,
            GROUP_CONCAT( warehouse_code SEPARATOR ',') as warehouse_codes
        ");
        $this->buildBasicQuery($params);
        $this->addFilterPlantCode($params, false);
        $this->query
            ->groupBy(['part_code', 'part_color_code', 'unit'])
            ->orderBy('part_code');
        if ($isPaginate) {
            return $this->query->paginate($limit);
        } else {
            return $this->query->get();
        }
    }

    /**
     * @param $warehouseType
     * @param $rows
     * @param bool $only_insert
     * @return void
     */
    public static function insertOrUpdateBulk($warehouseType, $rows, bool $only_insert = false)
    {
        $summaryData = [];
        foreach ($rows as $key => $row) {
            $summaryData[$key] = array_map(function ($key) use ($row) {
                return $row[$key];
            }, self::UNIQUE_KEYS);
        }

        $rowsExists = WarehouseInventorySummary::whereInMultiple(self::UNIQUE_KEYS, $summaryData)
            ->select(self::UNIQUE_KEYS)
            ->get()
            ->toArray();

        $valuesExists = array_map(function ($val) {
            return implode(', ', $val);
        }, $rowsExists);
        $valuesExists = array_unique($valuesExists);

        list($rowsInsert, $rowsUpdate) = self::getRowsInsertOrUpdate($rows, $summaryData, $valuesExists, $warehouseType);

        if ($only_insert === false) {
            foreach ($rowsUpdate as $qty => $data) {
                WarehouseInventorySummary::whereInMultiple(self::UNIQUE_KEYS, $data)
                    ->increment('quantity', (int)$qty);
            }
        }

        if (count($rowsInsert)) {
            WarehouseInventorySummary::query()->insert($rowsInsert);
        }
    }

    /**
     * @param $rows
     * @param $summaryData
     * @param $valuesExists
     * @param $warehouseType
     * @return array
     */
    public static function getRowsInsertOrUpdate($rows, $summaryData, $valuesExists, $warehouseType): array
    {
        $rowsInsert = [];
        $rowsUpdate = [];
        $loggedId = auth()->id();
        $now = Carbon::now()->toDateTimeString();
        foreach ($rows as $k => $row) {
            $v = implode(', ', $summaryData[$k]);
            if (!in_array($v, $valuesExists)) {
                $rowsInsert[] = [
                    'part_code' => $row['part_code'],
                    'part_color_code' => $row['part_color_code'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'],
                    'warehouse_type' => $warehouseType,
                    'warehouse_code' => $row['warehouse_code'],
                    'plant_code' => $row['plant_code'],
                    'created_by' => $loggedId,
                    'updated_by' => $loggedId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            } else {
                if (!isset($rowsUpdate[$row['quantity']]))
                    $rowsUpdate[$row['quantity']] = [];

                $rowsUpdate[$row['quantity']][] = [
                    $row['part_code'],
                    $row['part_color_code'],
                    $row['warehouse_code'],
                    $warehouseType,
                    $row['plant_code'],
                ];
            }
        }
        return [$rowsInsert, $rowsUpdate];
    }

    /**
     * @param $warehouseType
     * @param $part_code
     * @param $part_color_code
     * @param $warehouse_code
     * @param $plant_code
     * @param $quantity
     * @return false|int
     */
    public static function decrementQuantityOfPart($warehouseType, $plant_code, $warehouse_code, $part_code, $part_color_code, $quantity)
    {
        return WarehouseInventorySummary::query()
            ->where([
                'warehouse_type' => $warehouseType,
                'plant_code' => $plant_code,
                'warehouse_code' => $warehouse_code,
                'part_code' => $part_code,
                'part_color_code' => $part_color_code,
            ])->decrement('quantity', $quantity);
    }

    /**
     * @param array $row
     * @param array $rows
     * @param bool $isDecrement
     * @return void
     */
    public static function groupWarehouseSummaryByQuantity(array $row, array &$rows = [], bool $isDecrement = false)
    {
        $row['quantity'] = ($isDecrement ? -1 : 1) * $row['box_quantity'] * $row['part_quantity'];
        $data = array_map(function ($key) use ($row) {
            return $row[$key];
        }, self::UNIQUE_KEYS);
        $key = implode('-', $data);

        if (!isset($rows[$key])) {
            $rows[$key] = $row;
        } else {
            $rows[$key]['quantity'] += $row['quantity'];
        }
    }

    /**
     * @return array
     */
    public function getWarehouseCodes(): array
    {
        $params = request()->toArray();
        $query = WarehouseInventorySummary::query();
        if(isset($params['plant_code'])){
            $query->where('plant_code',$params['plant_code']);
        }
        $rows = $query->select('warehouse_code', 'warehouse_type')->distinct()
            ->orderBy('warehouse_type')
            ->get()
            ->toArray();
        $warehouseCodes = [];
        foreach ($rows as $row) {
            if ($row['warehouse_type'] == Warehouse::TYPE_PLANT_WH) {
                $warehouseCodes[Warehouse::PLANT_WAREHOUSE_CODE] = Warehouse::TYPE_PLANT_WH;
                break;
            } else {
                $warehouseCodes[$row['warehouse_code']] = $row['warehouse_type'];
            }
        }
        return $warehouseCodes;
    }
}
