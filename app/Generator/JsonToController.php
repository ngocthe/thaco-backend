<?php

namespace App\Generator;

use App\Generator\Creators\ControllerCreator;
use Exception;

class JsonToController extends Generator
{

    protected $methods;

    /**
     * @return JsonToController
     * @throws Exception
     */
    public function parse(): JsonToController
    {
        return $this;
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $migrationCreator = new ControllerCreator($this->schema);
        $migrationCreator->create();
    }
}
