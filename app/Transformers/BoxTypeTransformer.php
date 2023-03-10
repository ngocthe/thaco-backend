<?php

namespace App\Transformers;

use App\Models\BoxType;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class BoxTypeTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param BoxType $boxType
     * @return array
     */
    public function transform(BoxType $boxType): array
    {
        return [
            'id' => $boxType->id,
            'code' => $boxType->code,
			'part_code' => $boxType->part_code,
			'description' => $boxType->description,
			'weight' => $boxType->weight,
			'width' => $boxType->width,
			'height' => $boxType->height,
			'depth' => $boxType->depth,
			'quantity' => $boxType->quantity,
			'unit' => $boxType->unit,
			'plant_code' => $boxType->plant_code,
            'created_at' => $boxType->created_at->toIso8601String(),
            'updated_at' => $boxType->updated_at->toIso8601String()
        ];
    }
}
