<?php

namespace App\Transformers;

use App\Models\OrderCalendar;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class OrderCalendarTransformer extends TransformerAbstract
{
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user'
    ];

    /**
     * @param OrderCalendar $orderCalendar
     * @return array
     */
    public function transform(OrderCalendar $orderCalendar): array
    {
        return [
            'id' => $orderCalendar->id,
            'contract_code' => $orderCalendar->contract_code,
			'part_group' => $orderCalendar->part_group,
			'etd' => $orderCalendar->etd,
			'eta  ' => $orderCalendar->eta  ,
			'lead_time' => $orderCalendar->lead_time,
			'ordering_cycle' => $orderCalendar->ordering_cycle,
            'created_at' => $orderCalendar->created_at->toIso8601String(),
            'updated_at' => $orderCalendar->updated_at->toIso8601String()
        ];
    }

    /**
     * @param OrderCalendar $orderCalendar
     * @return Item
     */
    public function includeUser(OrderCalendar $orderCalendar): Item
    {
        $updatedBy = $orderCalendar->updatedBy;
        return $this->item($updatedBy, new AdminInfoTransformer);
    }
}
