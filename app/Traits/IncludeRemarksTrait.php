<?php

namespace App\Traits;

use App\Transformers\RemarkTransformer;
use League\Fractal\Resource\Collection;

trait IncludeRemarksTrait
{
    /**
     * @param $model
     * @return Collection
     */
    public function includeRemarks($model): Collection
    {
        $remarks = $model->remarkable;
        return $this->collection($remarks, new RemarkTransformer);
    }

}
