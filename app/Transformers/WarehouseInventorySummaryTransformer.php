<?php

namespace App\Transformers;

use App\Models\WarehouseInventorySummary;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class WarehouseInventorySummaryTransformer extends TransformerAbstract
{
    use IncludeUserTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user'
    ];

    /**
     * @param WarehouseInventorySummary $whInventorySummary
     * @return array
     */
    public function transform(WarehouseInventorySummary $whInventorySummary): array
    {
        return [
            'id' => $whInventorySummary->id,
            'part_code' => $whInventorySummary->part_code,
			'part_color_code' => $whInventorySummary->part_color_code,
			'quantity' => $whInventorySummary->quantity,
			'unit' => $whInventorySummary->unit,
			'warehouse_type' => $whInventorySummary->warehouse_type,
			'warehouse_code' => $whInventorySummary->warehouse_code,
			'plant_code' => $whInventorySummary->plant_code,
            'created_at' => $whInventorySummary->created_at->toIso8601String(),
            'updated_at' => $whInventorySummary->updated_at->toIso8601String()
        ];
    }

}
