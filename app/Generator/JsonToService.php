<?php

namespace App\Generator;

use App\Generator\Creators\ServiceCreator;
use Exception;

class JsonToService extends Generator
{
    /**
     * @return JsonToService
     */
    public function parse(): JsonToService
    {
        return $this;
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $migrationCreator = new ServiceCreator($this->schema);
        $migrationCreator->create();
    }
}
