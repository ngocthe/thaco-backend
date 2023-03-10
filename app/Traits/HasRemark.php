<?php

namespace App\Traits;

use App\Models\Remark;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasRemark
{
    /**
     * @return MorphMany
     */
    public function remarkable(): MorphMany
    {
        return $this->morphMany(Remark::class, 'modelable')->orderBy('created_at')->latest('id');
    }

}
