<?php

namespace App\Transformers;

use App\Models\OrderPointControl;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class OrderPointControlTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param OrderPointControl $orderingPointControl
     * @return array
     */
    public function transform(OrderPointControl $orderingPointControl): array
    {
        return [
            'id' => $orderingPointControl->id,
            'part_code' => $orderingPointControl->part_code,
			'part_color_code' => $orderingPointControl->part_color_code,
            'box_type_code' => $orderingPointControl->box_type_code,
			'standard_stock' => $orderingPointControl->standard_stock,
            'ordering_lot' => $orderingPointControl->ordering_lot,
			'plant_code' => $orderingPointControl->plant_code,
            'created_at' => $orderingPointControl->created_at->toIso8601String(),
            'updated_at' => $orderingPointControl->updated_at->toIso8601String()
        ];
    }

}
