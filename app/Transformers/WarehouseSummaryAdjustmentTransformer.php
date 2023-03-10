<?php

namespace App\Transformers;

use App\Models\WarehouseSummaryAdjustment;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class WarehouseSummaryAdjustmentTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;

    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param WarehouseSummaryAdjustment $warehouseSummaryAdjustment
     * @return array
     */
    public function transform(WarehouseSummaryAdjustment $warehouseSummaryAdjustment): array
    {
        return [
            'id' => $warehouseSummaryAdjustment->id,
            'warehouse_code' => $warehouseSummaryAdjustment->warehouse_code,
			'part_code' => $warehouseSummaryAdjustment->part_code,
			'part_color_code' => $warehouseSummaryAdjustment->part_color_code,
			'old_quantity' => $warehouseSummaryAdjustment->old_quantity,
			'new_quantity' => $warehouseSummaryAdjustment->new_quantity,
			'adjustment_quantity' => $warehouseSummaryAdjustment->adjustment_quantity,
			'plant_code' => $warehouseSummaryAdjustment->plant_code,
            'created_at' => $warehouseSummaryAdjustment->created_at->toIso8601String(),
            'updated_at' => $warehouseSummaryAdjustment->updated_at->toIso8601String(),
        ];
    }

}
