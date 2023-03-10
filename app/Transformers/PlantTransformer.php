<?php

namespace App\Transformers;

use App\Models\Plant;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class PlantTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user',
        'remarks'
    ];

    /**
     * @param Plant $plant
     * @return array
     */
    public function transform(Plant $plant): array
    {
        return [
            'id' => $plant->id,
            'code' => $plant->code,
			'description' => $plant->description,
            'created_at' => $plant->created_at->toIso8601String(),
            'updated_at' => $plant->updated_at->toIso8601String(),
        ];
    }

}
