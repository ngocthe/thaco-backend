<?php

namespace App\Transformers;

use App\Models\PartGroup;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class PartGroupTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param PartGroup $partGroup
     * @return array
     */
    public function transform(PartGroup $partGroup): array
    {
        return [
            'id' => $partGroup->id,
            'code' => $partGroup->code,
			'description' => $partGroup->description,
			'lead_time' => $partGroup->lead_time,
			'ordering_cycle' => $partGroup->ordering_cycle,
            'delivery_lead_time' => $partGroup->delivery_lead_time,
            'created_at' => $partGroup->created_at->toIso8601String(),
            'updated_at' => $partGroup->updated_at->toIso8601String()
        ];
    }

}
