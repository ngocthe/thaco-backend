<?php

namespace App\Transformers;

use App\Models\PartColor;
use App\Traits\IncludeEcnsTrait;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class PartColorTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait, IncludeEcnsTrait;

    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'ecn_in', 'ecn_out', 'remarks'
    ];

    /**
     * @param PartColor $partColor
     * @return array
     */
    public function transform(PartColor $partColor): array
    {
        return [
            'id' => $partColor->id,
            'code' => $partColor->code,
			'part_code' => $partColor->part_code,
			'name' => $partColor->name,
			'interior_code' => $partColor->interior_code,
			'vehicle_color_code' => $partColor->vehicle_color_code,
			'plant_code' => $partColor->plant_code,
            'created_at' => $partColor->created_at->toIso8601String(),
            'updated_at' => $partColor->updated_at->toIso8601String()
        ];
    }
}
