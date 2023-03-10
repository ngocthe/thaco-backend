<?php

namespace App\Generator;

use App\Generator\Creators\ModelCreator;
use Exception;

class JsonToModel extends Generator
{

    protected $methods;

    /**
     * @return JsonToModel
     * @throws Exception
     */
    public function parse(): JsonToModel
    {
        return $this;
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $migrationCreator = new ModelCreator($this->schema);
        $migrationCreator->create();
    }
}
