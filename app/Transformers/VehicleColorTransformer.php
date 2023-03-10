<?php

namespace App\Transformers;

use App\Models\VehicleColor;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class VehicleColorTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param VehicleColor $vehicleColor
     * @return array
     */
    public function transform(VehicleColor $vehicleColor): array
    {
        return [
            'id' => $vehicleColor->id,
            'code' => $vehicleColor->code,
			'type' => $vehicleColor->type,
			'name' => $vehicleColor->name,
			'ecn_in' => $vehicleColor->ecn_in,
			'ecn_out' => $vehicleColor->ecn_out,
			'plant_code' => $vehicleColor->plant_code,
            'created_at' => $vehicleColor->created_at->toIso8601String(),
            'updated_at' => $vehicleColor->updated_at->toIso8601String()
        ];
    }

}
