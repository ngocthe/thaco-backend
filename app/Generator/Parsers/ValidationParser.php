<?php

namespace App\Generator\Parsers;
use App\Generator\Traits\HasParameterChecks;

class ValidationParser
{
    use HasParameterChecks;
    protected $key = 'validation';
    protected $schema, $requests = [];

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return array
     */
    public function parse(): array
    {
        foreach ($this->schema as $table => $columns) {
            foreach ($columns as $column => $parameters) {
                if ($this->wantsParameter($parameters)) {
                    $this->requests[$table][$column] = $parameters[$this->key];
                }
            }
        }

        return $this->requests;
    }

}
