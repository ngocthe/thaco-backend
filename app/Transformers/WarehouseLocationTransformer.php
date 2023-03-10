<?php

namespace App\Transformers;

use App\Models\WarehouseLocation;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class WarehouseLocationTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param WarehouseLocation $warehouseLocation
     * @return array
     */
    public function transform(WarehouseLocation $warehouseLocation): array
    {
        return [
            'id' => $warehouseLocation->id,
            'code' => $warehouseLocation->code,
			'warehouse_code' => $warehouseLocation->warehouse_code,
			'description' => $warehouseLocation->description,
			'plant_code' => $warehouseLocation->plant_code,
            'created_at' => $warehouseLocation->created_at->toIso8601String(),
            'updated_at' => $warehouseLocation->updated_at->toIso8601String()
        ];
    }

}
