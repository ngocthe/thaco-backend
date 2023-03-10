<?php

namespace App\Transformers;

use App\Models\BwhOrderRequest;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class BwhOrderRequestTransformer extends TransformerAbstract
{
    use IncludeUserTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user'
    ];

    /**
     * @param BwhOrderRequest $bwhOrderRequest
     * @return array
     */
    public function transform(BwhOrderRequest $bwhOrderRequest): array
    {
        return [
            'id' => $bwhOrderRequest->id,
            'order_number' => $bwhOrderRequest->order_number,
            'contract_code' => $bwhOrderRequest->contract_code,
			'invoice_code' => $bwhOrderRequest->invoice_code,
			'bill_of_lading_code' => $bwhOrderRequest->bill_of_lading_code,
			'container_code' => $bwhOrderRequest->container_code,
			'case_code' => $bwhOrderRequest->case_code,
            'supplier_code' => $bwhOrderRequest->supplier_code,
			'part_code' => $bwhOrderRequest->part_code,
			'part_color_code' => $bwhOrderRequest->part_color_code,
			'box_type_code' => $bwhOrderRequest->box_type_code,
			'box_quantity' => $bwhOrderRequest->box_quantity,
			'part_quantity' => $bwhOrderRequest->part_quantity,
			'warehouse_code' => $bwhOrderRequest->warehouse_code,
			'warehouse_location_code' => $bwhOrderRequest->warehouse_location_code,
			'plant_code' => $bwhOrderRequest->plant_code,
            'created_at' => $bwhOrderRequest->created_at->toIso8601String(),
            'updated_at' => $bwhOrderRequest->updated_at->toIso8601String(),
        ];
    }

}
