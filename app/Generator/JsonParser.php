<?php

namespace App\Generator;

use Exception;

class JsonParser {
    /**
     * Path of the JSON schema
     *
     * @var string
     */
    protected $path;

    /**
     * @param String $path
     * @throws Exception
     */
    public function __construct(String $path) {
        $this->path = $path;
        $this->exists();
    }

    /**
     * Parse the JSON file into array
     * @return array
     */
    public function parse(): array
    {
        $json = $this->get();
        $schema = [];

        foreach($json as $table => $columns) {
            $schema[$table] = [];

            foreach($columns as $column => $parameters) {
                $parametersList = explode('|', $parameters['migration']);
                $parametersList = array_map(function($parameter) {
                    return explode(':', $parameter);
                }, $parametersList);

                $schema[$table][$column]['migration'] = $parametersList;
                if (isset($parameters['validation'])){
                    $schema[$table][$column]['validation'] = $parameters['validation'];
                }

                if(isset($parameters['filter'])){
                    $schema[$table][$column]['filter'] = $parameters['filter'];
                }

            }
        }
        return $schema;
    }

    /**
     * Load JSON from file
     * @return mixed
     */
    public function get() {
        $json = file_get_contents($this->path);
        return json_decode($json, true);
    }

    /**
     * Check if the path exists
     * @throws Exception
     */
    private function exists() {
        if(!file_exists($this->path)) throw new Exception("JSON Schema file does not exist.");
    }
}
