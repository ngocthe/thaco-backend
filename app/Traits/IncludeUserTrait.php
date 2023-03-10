<?php

namespace App\Traits;

use App\Transformers\AdminInfoTransformer;
use League\Fractal\Resource\Item;

trait IncludeUserTrait
{
    /**
     * @param $model
     * @return Item|null
     */
    public function includeUser($model): ?Item
    {
        $updatedBy = $model->updatedBy;
        return $updatedBy ? $this->item($updatedBy, new AdminInfoTransformer) : null;
    }

}
