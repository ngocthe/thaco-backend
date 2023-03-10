<?php

namespace App\Transformers;

use App\Models\VietnamSourceLog;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class VietnamSourceLogTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param VietnamSourceLog $vietnamSourceLog
     * @return array
     */
    public function transform(VietnamSourceLog $vietnamSourceLog): array
    {
        return [
            'id' => $vietnamSourceLog->id,
            'contract_code' => $vietnamSourceLog->contract_code,
			'invoice_code' => $vietnamSourceLog->invoice_code,
			'bill_of_lading_code' => $vietnamSourceLog->bill_of_lading_code,
			'container_code' => $vietnamSourceLog->container_code,
			'case_code' => $vietnamSourceLog->case_code,
			'part_code' => $vietnamSourceLog->part_code,
			'part_color_code' => $vietnamSourceLog->part_color_code,
			'box_type_code' => $vietnamSourceLog->box_type_code,
			'box_quantity' => $vietnamSourceLog->box_quantity,
			'part_quantity' => $vietnamSourceLog->part_quantity,
			'unit' => $vietnamSourceLog->unit,
			'supplier_code' => $vietnamSourceLog->supplier_code,
			'delivery_date' => $vietnamSourceLog->delivery_date,
			'plant_code' => $vietnamSourceLog->plant_code,
            'created_at' => $vietnamSourceLog->created_at->toIso8601String(),
            'updated_at' => $vietnamSourceLog->updated_at->toIso8601String(),
        ];
    }

}
