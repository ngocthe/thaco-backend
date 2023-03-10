<?php

namespace App\Generator\Creators;

use App\Generator\Helpers;
use Illuminate\Support\Str;

class TransformerCreator
{
    /**
     * Migration methods
     */
    protected $methods;

    /**
     * Create an instance of the Migration Creator
     *
     * @param array $methods
     * @return void
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    /**
     * @return void
     */
    public function create()
    {
        foreach ($this->methods as $table => $methods) {
            $this->createModel($table, $methods);
        }
    }

    /**
     * @param $table
     * @param $methods
     * @return void
     */
    private function createModel($table, $methods)
    {
        $modelName = Helpers::generateModelName($table);
        $filename = $this->generateFileName($modelName);
        $stub = $this->createStub($modelName, $methods);
        $path = $this->getPath($filename);
        file_put_contents($path, $stub);
    }

    /**
     * @param $modelName
     * @return string
     */
    private function generateFileName($modelName): string
    {
        return sprintf('%sTransformer.php', $modelName);
    }

    /**
     * @return false|string
     */
    private function getStub()
    {
        return file_get_contents(resource_path("stubs/transformer.stub"));
    }

    /**
     * @param $modelName
     * @param $methods
     * @return array|string|string[]
     */
    private function createStub($modelName, $methods)
    {
        $stub = $this->getStub();
        $stub = str_replace("{{modelName}}", $modelName, $stub);
        $modelNameVariable = '$' . lcfirst($modelName);
        $stub = str_replace("{{modelNameVariable}}", $modelNameVariable, $stub);
        return str_replace("{{transformerFields}}", $this->stringifyTransformerFields($methods, $modelNameVariable), $stub);
    }

    /**
     * @param $rules
     * @param $modelNameVariable
     * @return string
     */
    private function stringifyTransformerFields($rules, $modelNameVariable): string
    {
        $transformerFieldsStr = '';
        foreach ($rules as $key => $rule) {
            $transformerFieldsStr .= "'$key' => $modelNameVariable->$key,\n\t\t\t";
        }
        return rtrim($transformerFieldsStr, ",\n\t");
    }

    /**
     * @param $filename
     * @return string
     */
    private function getPath($filename): string
    {
        $dir = base_path("app/Transformers");
        return "$dir/$filename";
    }
}
