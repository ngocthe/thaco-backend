<?php

namespace App\Transformers;

use App\Models\Procurement;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class ProcurementTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param Procurement $procurement
     * @return array
     */
    public function transform(Procurement $procurement): array
    {
        return [
            'id' => $procurement->id,
            'part_code' => $procurement->part_code,
			'part_color_code' => $procurement->part_color_code,
			'minimum_order_quantity' => $procurement->minimum_order_quantity,
			'standard_box_quantity' => $procurement->standard_box_quantity,
			'part_quantity' => $procurement->part_quantity,
			'unit' => $procurement->unit,
			'supplier_code' => $procurement->supplier_code,
			'plant_code' => $procurement->plant_code,
            'created_at' => $procurement->created_at->toIso8601String(),
            'updated_at' => $procurement->updated_at->toIso8601String()
        ];
    }

}
