<?php

namespace App\Transformers;

use App\Models\PartUsageResult;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class PartUsageResultTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;

    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param PartUsageResult $partUsageResult
     * @return array
     */
    public function transform(PartUsageResult $partUsageResult): array
    {
        return [
            'id' => $partUsageResult->id,
            'used_date' => $partUsageResult->used_date,
			'part_code' => $partUsageResult->part_code,
			'part_color_code' => $partUsageResult->part_color_code,
			'plant_code' => $partUsageResult->plant_code,
			'quantity' => $partUsageResult->quantity,
            'created_at' => $partUsageResult->created_at->toIso8601String(),
            'updated_at' => $partUsageResult->updated_at->toIso8601String()
        ];
    }
}
