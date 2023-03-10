<?php

namespace App\Traits;

use App\Transformers\EcnTransformer;
use League\Fractal\Resource\Primitive;

trait IncludeEcnsTrait
{
    /**
     * @param $model
     * @return Primitive|null
     */
    public function includeEcnIn($model): ?Primitive
    {
        $ecn_in = $model->ecnInInfo;
        return $ecn_in ? new Primitive((new EcnTransformer())->transformWithCodeAndInDate($ecn_in)) : null;
    }

    /**
     * @param $model
     * @return Primitive|null
     */
    public function includeEcnOut($model): ?Primitive
    {
        $ecn_out = $model->ecnOutInfo;
        return $ecn_out ? new Primitive((new EcnTransformer())->transformWithCodeAndOutDate($ecn_out)) : null;
    }

}
