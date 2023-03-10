<?php

namespace App\Transformers;

use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use League\Fractal\TransformerAbstract;

class WarehouseInventorySummaryByPartTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected array $warehouseCodes;

    public function __construct($warehouseCodes)
    {
        $this->warehouseCodes = $warehouseCodes;
    }

    /**
     * @param WarehouseInventorySummary $whInventorySummary
     * @return array
     */
    public function transform(WarehouseInventorySummary $whInventorySummary): array
    {
        return [
            'part_code' => $whInventorySummary->part_code,
			'part_color_code' => $whInventorySummary->part_color_code,
			'unit' => $whInventorySummary->unit,
            'warehouses' => $this->getWarehousesQuantity($whInventorySummary)
        ];
    }

    /**
     * @param WarehouseInventorySummary $whInventorySummary
     * @return array
     */
    public function getWarehousesQuantity(WarehouseInventorySummary $whInventorySummary): array
    {
        $warehouseCodes = explode(',', $whInventorySummary->warehouse_codes);
        $quantity = explode(',', $whInventorySummary->quantity);
        $warehouses = [];
        foreach ($this->warehouseCodes as $warehouseCode => $warehouseType) {
            $warehouses[$warehouseCode] = [
                'warehouse_code' => $warehouseCode,
                'quantity' => 0
            ];
        }

        foreach ($warehouseCodes as $key => $warehouseCode) {
            if (!isset($this->warehouseCodes[$warehouseCode]) || $warehouseCode == Warehouse::PLANT_WAREHOUSE_CODE) {
                $warehouseCode = Warehouse::PLANT_WAREHOUSE_CODE;
            }
            $warehouses[$warehouseCode]['quantity'] += $quantity[$key];
        }
        return array_values($warehouses);
    }
}
