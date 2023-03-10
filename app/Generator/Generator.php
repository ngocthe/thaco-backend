<?php
namespace App\Generator;

abstract class Generator implements GeneratorInterface {
    /**
     * @var
     */
    public $schema;

    /**
     * @param $schema
     */
    public function __construct($schema) {
        $this->schema = $schema;
        $this->parse()->create();
    }

}
