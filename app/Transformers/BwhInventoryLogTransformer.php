<?php

namespace App\Transformers;

use App\Models\BwhInventoryLog;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class BwhInventoryLogTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param BwhInventoryLog $bwhInventoryLog
     * @return array
     */
    public function transform(BwhInventoryLog $bwhInventoryLog): array
    {
        return [
            'id' => $bwhInventoryLog->id,
            'contract_code' => $bwhInventoryLog->contract_code,
			'invoice_code' => $bwhInventoryLog->invoice_code ,
			'bill_of_lading_code' => $bwhInventoryLog->bill_of_lading_code,
			'container_code' => $bwhInventoryLog->container_code,
			'case_code' => $bwhInventoryLog->case_code,
			'part_code' => $bwhInventoryLog->part_code,
			'part_color_code' => $bwhInventoryLog->part_color_code,
			'box_type_code' => $bwhInventoryLog->box_type_code,
			'box_quantity' => $bwhInventoryLog->box_quantity,
			'part_quantity' => $bwhInventoryLog->part_quantity,
			'unit' => $bwhInventoryLog->unit,
			'supplier_code' => $bwhInventoryLog->supplier_code,
            'container_received' => $bwhInventoryLog->container_received ? $bwhInventoryLog->container_received->toDateString() : null,
			'devanned_date' => $bwhInventoryLog->devanned_date ? $bwhInventoryLog->devanned_date->toDateString() : null,
			'stored_date' => $bwhInventoryLog->stored_date ? $bwhInventoryLog->stored_date->toDateString() : null,
			'warehouse_location_code' => $bwhInventoryLog->warehouse_location_code,
            'warehouse_code' => $bwhInventoryLog->warehouse_code,
			'shipped_date' => $bwhInventoryLog->shipped_date ? $bwhInventoryLog->shipped_date->toDateString() : null,
            'defect_id' => $bwhInventoryLog->defect_id,
			'plant_code' => $bwhInventoryLog->plant_code,
            'created_at' => $bwhInventoryLog->created_at->toIso8601String(),
            'updated_at' => $bwhInventoryLog->updated_at->toIso8601String(),
            'order_number' => $bwhInventoryLog->order_number
        ];
    }

}
