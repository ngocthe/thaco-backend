<?php

namespace App\Generator;

use App\Generator\Creators\MigrationCreator;
use App\Generator\Parsers\SchemaParser;
use Exception;

class JsonToMigration extends Generator
{

    protected $methods;

    /**
     * @return $this
     * @throws Exception
     */
    public function parse(): JsonToMigration
    {
        $schemaParser = new SchemaParser($this->schema);
        $this->methods = $schemaParser->parse();
        return $this;
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $migrationCreator = new MigrationCreator($this->methods);
        $migrationCreator->create();
    }
}
