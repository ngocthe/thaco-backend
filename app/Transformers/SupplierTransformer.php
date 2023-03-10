<?php

namespace App\Transformers;

use App\Models\Supplier;
use App\Traits\IncludeRemarksTrait;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class SupplierTransformer extends TransformerAbstract
{
    use IncludeUserTrait, IncludeRemarksTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user', 'remarks'
    ];

    /**
     * @param Supplier $supplier
     * @return array
     */
    public function transform(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id,
            'code' => $supplier->code,
			'description' => $supplier->description,
			'address' => $supplier->address,
			'phone' => $supplier->phone,
			'forecast_by_week' => $supplier->forecast_by_week,
			'forecast_by_month' => $supplier->forecast_by_month,
            'receiver' => $supplier->receiver ? json_decode($supplier->receiver) : null,
            'bcc' => $supplier->bcc ? json_decode($supplier->bcc) : null,
            'cc' => $supplier->cc ? json_decode($supplier->cc) : null,
            'created_at' => $supplier->created_at->toIso8601String(),
            'updated_at' => $supplier->updated_at->toIso8601String()
        ];
    }

}
