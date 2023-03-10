<?php

namespace App\Transformers;

use App\Models\OrderList;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class OrderListTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param OrderList $orderList
     * @return array
     */
    public function transform(OrderList $orderList): array
    {
        return [
            'id' => $orderList->id,
            'status' => $orderList->status,
			'contract_code' => $orderList->contract_code,
			'eta' => $orderList->eta,
			'part_code' => $orderList->part_code,
			'part_color_code' => $orderList->part_color_code,
			'part_group' => $orderList->part_group,
			'actual_quantity' => $orderList->actual_quantity,
			'supplier_code' => $orderList->supplier_code,
			'import_id' => $orderList->import_id,
			'moq' => $orderList->moq,
			'mrp_quantity' => $orderList->mrp_quantity,
			'file_import' => $orderList->fileImport,
			'plant_code' => $orderList->plant_code,
            'created_at' => $orderList->created_at->toIso8601String(),
            'updated_at' => $orderList->updated_at->toIso8601String(),
        ];
    }

}
