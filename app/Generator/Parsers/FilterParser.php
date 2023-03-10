<?php

namespace App\Generator\Parsers;
use App\Generator\Traits\HasParameterChecks;

class FilterParser
{
    use HasParameterChecks;
    protected $key = 'filter';
    protected $schema, $filters = [];

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return array
     */
    public function parse(): array
    {
        foreach ($this->schema as $column => $parameters) {
            foreach ($parameters as $key => $options) {
                if ($key == $this->key) {
                    $this->filters[$column] = $options;
                }
            }
        }
        return $this->filters;
    }

}
