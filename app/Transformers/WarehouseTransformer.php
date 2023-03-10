<?php

namespace App\Transformers;

use App\Models\Warehouse;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class WarehouseTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param Warehouse $warehouse
     * @return array
     */
    public function transform(Warehouse $warehouse): array
    {
        return [
            'id' => $warehouse->id,
            'code' => $warehouse->code,
			'description' => $warehouse->description,
            'warehouse_type' => $warehouse->warehouse_type,
			'plant_code' => $warehouse->plant_code,
            'created_at' => $warehouse->created_at->toIso8601String(),
            'updated_at' => $warehouse->updated_at->toIso8601String()
        ];
    }

}
