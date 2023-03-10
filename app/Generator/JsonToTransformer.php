<?php

namespace App\Generator;

use App\Generator\Creators\TransformerCreator;
use Exception;

class JsonToTransformer extends Generator
{

    protected $methods;

    /**
     * @return JsonToTransformer
     * @throws Exception
     */
    public function parse(): JsonToTransformer
    {
        return $this;
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $migrationCreator = new TransformerCreator($this->schema);
        $migrationCreator->create();
    }
}
