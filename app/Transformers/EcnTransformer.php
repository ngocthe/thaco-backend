<?php

namespace App\Transformers;

use App\Models\Ecn;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class EcnTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;

    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param Ecn $ecn
     * @return array
     */
    public function transform(Ecn $ecn): array
    {
        return [
            'id' => $ecn->id,
            'code' => $ecn->code,
			'page_number' => $ecn->page_number,
			'line_number' => $ecn->line_number,
			'description' => $ecn->description,
			'mandatory_level' => $ecn->mandatory_level,
			'production_interchangeability' => $ecn->production_interchangeability,
			'service_interchangeability' => $ecn->service_interchangeability,
			'released_party' => $ecn->released_party,
			'released_date' => $ecn->released_date ? $ecn->released_date->format('Y-m-d') : null,
			'planned_line_off_date' => $ecn->planned_line_off_date ? $ecn->planned_line_off_date->format('Y-m-d') : null,
			'actual_line_off_date' => $ecn->actual_line_off_date ? $ecn->actual_line_off_date->format('Y-m-d') : null,
			'planned_packing_date' => $ecn->planned_packing_date ? $ecn->planned_packing_date->format('Y-m-d') : null,
			'actual_packing_date' => $ecn->actual_packing_date ? $ecn->actual_packing_date->format('Y-m-d') : null,
			'vin' => $ecn->vin,
			'complete_knockdown' => $ecn->complete_knockdown,
			'plant_code' => $ecn->plant_code,
            'created_at' => $ecn->created_at->toIso8601String(),
            'updated_at' => $ecn->updated_at->toIso8601String()
        ];
    }

    /**
     * @param Ecn $ecn
     * @return array
     */
    public function transformWithCodeAndInDate(Ecn $ecn): array
    {
        return [
            'code' => $ecn->code,
            'actual_packing_date' => $ecn->actual_line_off_date ? $ecn->actual_line_off_date->toIso8601String() : null
        ];
    }

    /**
     * @param Ecn $ecn
     * @return array
     */
    public function transformWithCodeAndOutDate(Ecn $ecn): array
    {
        return [
            'code' => $ecn->code,
            'actual_line_off_date' => $ecn->actual_line_off_date ? $ecn->actual_line_off_date->toIso8601String() : null
        ];
    }
}
