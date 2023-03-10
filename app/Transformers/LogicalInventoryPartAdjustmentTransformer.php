<?php

namespace App\Transformers;

use App\Models\LogicalInventoryPartAdjustment;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class LogicalInventoryPartAdjustmentTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param LogicalInventoryPartAdjustment $logicalInventoryPartAdjustment
     * @return array
     */
    public function transform(LogicalInventoryPartAdjustment $logicalInventoryPartAdjustment): array
    {
        return [
            'id' => $logicalInventoryPartAdjustment->id,
            'adjustment_date' => $logicalInventoryPartAdjustment->adjustment_date,
			'part_code' => $logicalInventoryPartAdjustment->part_code,
			'part_color_code' => $logicalInventoryPartAdjustment->part_color_code,
			'old_quantity' => $logicalInventoryPartAdjustment->old_quantity,
			'new_quantity' => $logicalInventoryPartAdjustment->new_quantity,
			'adjustment_quantity' => $logicalInventoryPartAdjustment->adjustment_quantity,
			'plant_code' => $logicalInventoryPartAdjustment->plant_code,
            'created_at' => $logicalInventoryPartAdjustment->created_at->toIso8601String(),
            'updated_at' => $logicalInventoryPartAdjustment->updated_at->toIso8601String(),
        ];
    }

}
