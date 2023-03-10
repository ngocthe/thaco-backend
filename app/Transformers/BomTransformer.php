<?php

namespace App\Transformers;

use App\Models\Bom;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class BomTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param Bom $bom
     * @return array
     */
    public function transform(Bom $bom): array
    {
        return [
            'id' => $bom->id,
            'msc_code' => $bom->msc_code,
			'shop_code' => $bom->shop_code,
			'part_code' => $bom->part_code,
			'part_color_code' => $bom->part_color_code,
			'quantity' => $bom->quantity,
			'ecn_in' => $bom->ecn_in,
			'ecn_out' => $bom->ecn_out,
			'plant_code' => $bom->plant_code,
            'part_remarks'=> $bom->part_remarks,
            'created_at' => $bom->created_at->toIso8601String(),
            'updated_at' => $bom->updated_at->toIso8601String()
        ];
    }

}
