<?php

namespace App\Transformers;

use App\Models\UpkwhInventoryLog;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class UpkwhInventoryLogTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks', 'defects'
    ];

    /**
     * @param UpkwhInventoryLog $upkwhInventoryLog
     * @return array
     */
    public function transform(UpkwhInventoryLog $upkwhInventoryLog): array
    {
        return [
            'id' => $upkwhInventoryLog->id,
            'contract_code' => $upkwhInventoryLog->contract_code,
			'invoice_code' => $upkwhInventoryLog->invoice_code,
			'bill_of_lading_code' => $upkwhInventoryLog->bill_of_lading_code,
			'container_code' => $upkwhInventoryLog->container_code,
			'case_code' => $upkwhInventoryLog->case_code,
			'part_code' => $upkwhInventoryLog->part_code,
			'part_color_code' => $upkwhInventoryLog->part_color_code,
			'box_type_code' => $upkwhInventoryLog->box_type_code,
			'box_quantity' => $upkwhInventoryLog->box_quantity,
			'part_quantity' => $upkwhInventoryLog->part_quantity,
			'unit' => $upkwhInventoryLog->unit,
			'supplier_code' => $upkwhInventoryLog->supplier_code,
			'received_date' => $upkwhInventoryLog->received_date ? $upkwhInventoryLog->received_date->toDateString() : null,
			'shelf_location_code' => $upkwhInventoryLog->shelf_location_code,
            'warehouse_code' => $upkwhInventoryLog->warehouse_code,
			'shipped_box_quantity' => $upkwhInventoryLog->shipped_box_quantity,
			'shipped_date' => $upkwhInventoryLog->shipped_date ? $upkwhInventoryLog->shipped_date->toDateString() : null,
            'defect_id' => $upkwhInventoryLog->defect_id,
			'plant_code' => $upkwhInventoryLog->plant_code,
            'remark' => $upkwhInventoryLog->remark,
            'created_at' => $upkwhInventoryLog->created_at->toIso8601String(),
            'updated_at' => $upkwhInventoryLog->updated_at->toIso8601String()
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
