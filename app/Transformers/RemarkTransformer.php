<?php

namespace App\Transformers;

use App\Models\Remark;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class RemarkTransformer extends TransformerAbstract
{
    use IncludeUserTrait;
    /**
     * @var array|string[]
     */
    protected array $defaultIncludes = [
        'user'
    ];
    /**
     * @param Remark $remark
     * @return array
     */

    public function transform(Remark $remark): array
    {
        return [
            'id' => $remark->id,
            'content' => $remark->content,
            'created_at' => $remark->created_at->toIso8601String(),
            'updated_at' => $remark->updated_at->toIso8601String(),
        ];
    }

}
