<?php

namespace App\Generator\Traits;

trait HasParameterChecks
{
    /**
     * @param $parameters
     * @return bool
     */
    public function wantsParameter($parameters): bool
    {
        return isset($parameters[$this->key]);
    }
}
