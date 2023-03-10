<?php

namespace App\Generator\Creators;

use App\Generator\Helpers;
use Illuminate\Support\Str;

class ModelCreator
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
        return sprintf('%s.php', $modelName);
    }

    /**
     * @return false|string
     */
    private function getStub()
    {
        return file_get_contents(resource_path("stubs/model.stub"));
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
        return str_replace("{{modelFillable}}", $this->stringifyFillable($methods), $stub);
    }

    /**
     * @param $rules
     * @return string
     */
    private function stringifyFillable($rules): string
    {
        $fillableStr = '';
        foreach ($rules as $key => $rule) {
            $fillableStr .= "'$key',\n\t\t";
        }
        return rtrim($fillableStr, ",\n\t");
    }

    /**
     * @param $filename
     * @return string
     */
    private function getPath($filename): string
    {
        $dir = base_path("app/Models");
        return "$dir/$filename";
    }
}
