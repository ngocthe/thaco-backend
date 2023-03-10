<?php

namespace App\Transformers;

use App\Models\LogicalInventoryMscAdjustment;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class LogicalInventoryMscAdjustmentTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param LogicalInventoryMscAdjustment $logicalInventoryMscAdjustment
     * @return array
     */
    public function transform(LogicalInventoryMscAdjustment $logicalInventoryMscAdjustment): array
    {
        return [
            'id' => $logicalInventoryMscAdjustment->id,
            'msc_code' => $logicalInventoryMscAdjustment->msc_code,
			'adjustment_quantity' => $logicalInventoryMscAdjustment->adjustment_quantity,
			'vehicle_color_code' => $logicalInventoryMscAdjustment->vehicle_color_code,
			'production_date' => $logicalInventoryMscAdjustment->production_date,
			'plant_code' => $logicalInventoryMscAdjustment->plant_code,
            'created_at' => $logicalInventoryMscAdjustment->created_at->toIso8601String(),
            'updated_at' => $logicalInventoryMscAdjustment->updated_at->toIso8601String(),
        ];
    }
}
