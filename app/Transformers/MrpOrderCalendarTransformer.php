<?php

namespace App\Transformers;

use App\Models\MrpOrderCalendar;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class MrpOrderCalendarTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param MrpOrderCalendar $mrpOrderCalendar
     * @return array
     */
    public function transform(MrpOrderCalendar $mrpOrderCalendar): array
    {
        return [
            'id' => $mrpOrderCalendar->id,
            'contract_code' => $mrpOrderCalendar->contract_code,
			'part_group' => $mrpOrderCalendar->part_group,
			'etd' => $mrpOrderCalendar->etd,
			'eta' => $mrpOrderCalendar->eta,
			'target_plan_from' => $mrpOrderCalendar->target_plan_from,
			'target_plan_to' => $mrpOrderCalendar->target_plan_to,
			'buffer_span_from' => $mrpOrderCalendar->buffer_span_from,
			'buffer_span_to' => $mrpOrderCalendar->buffer_span_to,
			'order_span_from' => $mrpOrderCalendar->order_span_from,
			'order_span_to' => $mrpOrderCalendar->order_span_to,
			'mrp_or_run' => $mrpOrderCalendar->mrp_or_run,
            'created_at' => $mrpOrderCalendar->created_at->toIso8601String(),
            'updated_at' => $mrpOrderCalendar->updated_at->toIso8601String(),
            'status' => $mrpOrderCalendar->status,
        ];
    }

}
