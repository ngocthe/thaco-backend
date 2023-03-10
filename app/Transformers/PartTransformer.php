<?php

namespace App\Transformers;

use App\Models\Part;
use App\Traits\IncludeEcnsTrait;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class PartTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait, IncludeEcnsTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'ecn_in', 'ecn_out', 'remarks'
    ];

    /**
     * @param Part $part
     * @return array
     */
    public function transform(Part $part): array
    {
        return [
            'id' => $part->id,
            'code' => $part->code,
			'name' => $part->name,
			'group' => $part->group,
			'plant_code' => $part->plant_code,
            'created_at' => $part->created_at->toIso8601String(),
            'updated_at' => $part->updated_at->toIso8601String()
        ];
    }

}
