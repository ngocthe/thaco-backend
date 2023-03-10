<?php

namespace App\Generator\Creators;

use App\Generator\Helpers;
use App\Generator\Parsers\FilterParser;
use Illuminate\Support\Str;

class ServiceCreator
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
            $this->createService($table, $methods);
        }
    }

    /**
     * @param $table
     * @param $methods
     * @return void
     */
    private function createService($table, $methods)
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
        return sprintf('%sService.php', $modelName);
    }

    /**
     * @return false|string
     */
    private function getStub()
    {
        return file_get_contents(resource_path("stubs/service.stub"));
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
        return str_replace("{{filter}}", $this->stringifyFilter($methods), $stub);
    }

    /**
     * @param $methods
     * @return string
     */
    private function stringifyFilter($methods): string
    {
        $validationParser = new FilterParser($methods);
        $filters = $validationParser->parse();
        $filterStr = '';
        foreach ($filters as $key => $rule) {
            switch ($rule){
                case $rule === 'like':
                    $filterStr .= $this->stringifyFilterLike($key) . "\n\t\t";
                    break;
                case $rule === 'in':
                    $filterStr .= $this->stringifyFilterIn($key) . "\n\t\t";
                    break;
                case $rule === 'lt':
                    $filterStr .= $this->stringifyFilterLessThan($key) . "\n\t\t";
                    break;
                case $rule === 'gt':
                    $filterStr .= $this->stringifyFilterGreaterThan($key) . "\n\t\t";
                    break;
                case $rule === 'range':
                    $filterStr .= $this->stringifyFilterRange($key) . "\n\t\t";
                    break;
                default:
                    $filterStr .= $this->stringifyFilterEqual($key) . "\n\t\t";
            }
        }
        return $filterStr;
    }

    /**
     * @param $key
     * @return string
     */
    private function stringifyFilterLike($key): string
    {
        return str_replace('{{key}}', $key, '
        if (isset($params[\'{{key}}\']) && $this->checkParamFilter($params[\'{{key}}\'])) {
            $this->query->where(\'{{key}}\', \'LIKE\', \'%\' . $params[\'{{key}}\']. \'%\');
        }');
    }

    /**
     * @param $key
     * @return string
     */
    private function stringifyFilterEqual($key): string
    {
        return str_replace('{{key}}', $key, '
        if (isset($params[\'{{key}}\']) && $this->checkParamFilter($params[\'{{key}}\'])) {
            $this->query->where(\'{{key}}\', $params[\'{{key}}\']);
        }');
    }

    /**
     * @param $key
     * @return string
     */
    private function stringifyFilterLessThan($key): string
    {
        return str_replace('{{key}}', $key, '
        if (isset($params[\'{{key}}\']) && $this->checkParamFilter($params[\'{{key}}\'])) {
            $this->query->where(\'{{key}}\', \'<=\', $params[\'{{key}}\']);
        }');
    }

    /**
     * @param $key
     * @return string
     */
    private function stringifyFilterGreaterThan($key): string
    {
        return str_replace('{{key}}', $key, '
        if (isset($params[\'{{key}}\']) && $this->checkParamFilter($params[\'{{key}}\'])) {
            $this->query->where(\'{{key}}\', \'>=\', $params[\'{{key}}\']);
        }');
    }

    /**
     * @param $key
     * @return string
     */
    private function stringifyFilterRange($key): string
    {
        return str_replace('{{key}}', $key, '
        if (isset($params[\'{{key}}\']) && $this->checkParamFilter($params[\'{{key}}\'])) {
            $value = explode(\',\', $params[\'{{key}}\']);
            if (count($value) == 2) {
                $this->query->whereBetween(\'{{key}}\', $value);
            }
        }');
    }

    /**
     * @param $key
     * @return string
     */
    private function stringifyFilterIn($key): string
    {
        return str_replace('{{key}}', $key, '
        if (isset($params[\'{{key}}\']) && $this->checkParamFilter($params[\'{{key}}\'])) {
            $this->query->whereIn(\'{{key}}\', array_map(\'trim\', explode(\',\', $params[\'{{key}}\'])));
        }');
    }

    /**
     * @param $filename
     * @return string
     */
    private function getPath($filename): string
    {
        $dir = base_path("app/Services");
        return "$dir/$filename";
    }
}
