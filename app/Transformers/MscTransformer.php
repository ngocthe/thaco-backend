<?php

namespace App\Transformers;

use App\Models\Msc;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class MscTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param Msc $msc
     * @return array
     */
    public function transform(Msc $msc): array
    {
        return [
            'id' => $msc->id,
            'code' => $msc->code,
			'description' => $msc->description,
			'interior_color' => $msc->interior_color,
			'car_line' => $msc->car_line,
			'model_grade' => $msc->model_grade,
			'body' => $msc->body,
			'engine' => $msc->engine,
			'transmission' => $msc->transmission,
			'plant_code' => $msc->plant_code,
			'effective_date_in' => $msc->effective_date_in ? $msc->effective_date_in->format('Y-m-d') : null,
			'effective_date_out' => $msc->effective_date_out ? $msc->effective_date_out->format('Y-m-d') : null,
            'created_at' => $msc->created_at->toIso8601String(),
            'updated_at' => $msc->updated_at->toIso8601String()
        ];
    }

}
