<?php

namespace App\Transformers;

use App\Models\LogicalInventory;
use App\Models\Warehouse;
use League\Fractal\TransformerAbstract;

class LogicalInventoryByPartTransformer extends TransformerAbstract
{

    /**
     * @param LogicalInventory $logicalInventory
     * @return array
     */
    public function transform(LogicalInventory $logicalInventory): array
    {
        return [
            'part_code' => $logicalInventory->part_code,
			'part_color_code' => $logicalInventory->part_color_code,
			'plant_code' => $logicalInventory->plant_code,
            'quantity' => explode(',', $logicalInventory->logical_quantities)[0],
            'warehouses' => $this->getWarehousesQuantity($logicalInventory)
        ];
    }

    /**
     * @param LogicalInventory $logicalInventory
     * @return array
     */
    private function getWarehousesQuantity(LogicalInventory $logicalInventory): array
    {
        $warehouseType = explode(',', $logicalInventory->warehouse_types);
        $arrayQuantities = explode(',', $logicalInventory->quantities);
        $warehouses = [
            'bwh' => 0,
            'upkwh' => 0,
            'plant_wh' => 0
        ];
        foreach ($arrayQuantities as $key => $quantity) {
            if($warehouseType[$key] == Warehouse::TYPE_BWH) {
                $warehouses['bwh'] += intval($quantity);
            }else if($warehouseType[$key] == Warehouse::TYPE_UPKWH) {
                $warehouses['upkwh'] += intval($quantity);
            }else if($warehouseType[$key] == Warehouse::TYPE_PLANT_WH){
                $warehouses['plant_wh'] += intval($quantity);
            }
        }
        return $warehouses;
    }
}
