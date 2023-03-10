<?php

namespace App\Transformers;

use App\Models\PlantInventoryLog;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class PlantInventoryLogTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks', 'defects'
    ];

    /**
     * @param PlantInventoryLog $plantInventoryLog
     * @return array
     */
    public function transform(PlantInventoryLog $plantInventoryLog): array
    {
        return [
            'id' => $plantInventoryLog->id,
            'part_code' => $plantInventoryLog->part_code,
			'part_color_code' => $plantInventoryLog->part_color_code,
			'box_type_code' => $plantInventoryLog->box_type_code,
			'received_date' => $plantInventoryLog->received_date ? $plantInventoryLog->received_date->toDateString() : null,
			'quantity' => $plantInventoryLog->quantity,
            'received_box_quantity' => $plantInventoryLog->received_box_quantity,
			'unit' => $plantInventoryLog->unit,
			'warehouse_code' => $plantInventoryLog->warehouse_code,
            'defect_id' => $plantInventoryLog->defect_id,
			'plant_code' => $plantInventoryLog->plant_code,
            'created_at' => $plantInventoryLog->created_at->toIso8601String(),
            'updated_at' => $plantInventoryLog->updated_at->toIso8601String(),
        ];
    }

    /**
     * @param $model
     * @return Collection
     */
    public function includeDefects($model): Collection
    {
        $remarks = $model->defectable;
        return $this->collection($remarks, new DefectInventoryTransformer);
    }

}
