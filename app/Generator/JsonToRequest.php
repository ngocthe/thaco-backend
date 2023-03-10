<?php

namespace App\Generator;

use App\Generator\Creators\RequestCreator;
use App\Generator\Parsers\ValidationParser;

class JsonToRequest extends Generator
{

    protected $requests;

    /**
     * @return $this
     */
    public function parse(): JsonToRequest
    {
        $validationParser = new ValidationParser($this->schema);
        $this->requests = $validationParser->parse();
        return $this;
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $requestCreator = new RequestCreator($this->requests);
        $requestCreator->create();
    }
}
