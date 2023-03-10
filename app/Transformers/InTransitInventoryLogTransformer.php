<?php

namespace App\Transformers;

use App\Models\InTransitInventoryLog;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class InTransitInventoryLogTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param InTransitInventoryLog $inTransitInventoryLog
     * @return array
     */
    public function transform(InTransitInventoryLog $inTransitInventoryLog): array
    {
        return [
            'id' => $inTransitInventoryLog->id,
            'contract_code' => $inTransitInventoryLog->contract_code,
			'invoice_code' => $inTransitInventoryLog->invoice_code ,
			'bill_of_lading_code' => $inTransitInventoryLog->bill_of_lading_code,
			'container_code' => $inTransitInventoryLog->container_code,
			'case_code' => $inTransitInventoryLog->case_code,
			'part_code' => $inTransitInventoryLog->part_code,
			'part_color_code' => $inTransitInventoryLog->part_color_code,
			'box_type_code' => $inTransitInventoryLog->box_type_code,
			'box_quantity' => $inTransitInventoryLog->box_quantity,
			'part_quantity' => $inTransitInventoryLog->part_quantity,
			'unit' => $inTransitInventoryLog->unit,
			'supplier_code' => $inTransitInventoryLog->supplier_code,
			'etd' => $inTransitInventoryLog->etd ? $inTransitInventoryLog->etd->format('Y-m-d') : null,
			'container_shipped' => $inTransitInventoryLog->container_shipped ? $inTransitInventoryLog->container_shipped->format('Y-m-d') : null,
			'eta' => $inTransitInventoryLog->eta ? $inTransitInventoryLog->eta->format('Y-m-d') : null,
			'plant_code' => $inTransitInventoryLog->plant_code,
            'created_at' => $inTransitInventoryLog->created_at->toIso8601String(),
            'updated_at' => $inTransitInventoryLog->updated_at->toIso8601String()
        ];
    }

}
