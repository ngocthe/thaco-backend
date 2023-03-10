<?php

namespace App\Generator\Creators;

use App\Generator\Helpers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ControllerCreator
{
    /**
     * Migration methods
     */
    protected array $controllers;
    protected const ADMIN_FOLDER = 'Admin';
    protected const APP_FOLDER = 'App';
    protected const ROUTE_ADMIN = 'api_admin';
    protected const ROUTE_APP = 'api';
    protected const LIST_PERMISSIONS = ['list', 'create', 'view', 'edit', 'delete'];

    /**
     * Create an instance of the Migration Creator
     *
     * @param array $controllers
     * @return void
     */
    public function __construct(array $controllers)
    {
        $this->controllers = $controllers;
    }

    /**
     * @return void
     */
    public function create()
    {
        foreach ($this->controllers as $table => $columns) {
            $modelName = $this->createController($table, $columns);
            $this->addPermission($modelName, $table);
            $this->addRoute($modelName, $table);
        }
    }

    /**
     * @param $table
     * @param $columns
     * @return string
     */
    private function createController($table, $columns): string
    {
        $modelName = Helpers::generateModelName($table);
        $filename = $this->generateFileName($modelName);
        $domainFolder = $this->getDomainFolder();
        $stub = $this->createStub($modelName, $domainFolder, $columns);
        $path = $this->getPath($filename, $domainFolder);
        file_put_contents($path, $stub);
        return $modelName;
    }

    /**
     * @return string
     */
    private function getDomainFolder(): string
    {
        return config('app.domain') === 'frontend' ? self::APP_FOLDER : self::ADMIN_FOLDER;
    }

    /**
     * @param $modelName
     * @return string
     */
    private function generateFileName($modelName): string
    {
        return sprintf('%sController.php', $modelName);
    }

    /**
     * @return false|string
     */
    private function getStub()
    {
        return file_get_contents(resource_path("stubs/crud-controller.stub"));
    }

    /**
     * @param $modelName
     * @param $domainFolder
     * @param $columns
     * @return array|string|string[]
     */
    private function createStub($modelName, $domainFolder, $columns)
    {
        $stub = $this->getStub();
        $stub = str_replace("{{modelName}}", $modelName, $stub);
        $stub = str_replace("{{domainFolder}}", $domainFolder, $stub);
        $stub = str_replace("{{moduleNameLowerCase}}", lcfirst($modelName), $stub);
        $stub = str_replace("{{moduleNamePluralLowerCase}}", Str::plural(lcfirst($modelName)), $stub);
        return str_replace("{{fields}}", $this->stringifyFields($columns), $stub);
    }

    /**
     * @param $columns
     * @return string
     */
    private function stringifyFields($columns): string
    {
        $fieldsStr = '';
        foreach ($columns as $key => $parameters) {
            $fieldsStr .= "'$key',\n\t\t\t";
        }
        return rtrim($fieldsStr, ",\n\t");
    }

    /**
     * @param $filename
     * @param $domainFolder
     * @return string
     */
    private function getPath($filename, $domainFolder): string
    {
        $dir = base_path("app/Http/Controllers/$domainFolder");
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }
        return "$dir/$filename";
    }

    /**
     * @param $modelName
     * @param $table
     * @return void
     */
    public function addPermission($modelName, $table)
    {
        $path = "app/Constants/Permission.php";
        $oldFilePath = base_path($path);
        $modelNameLower = lcfirst($modelName);
        $modelNameUpper = Helpers::generateModelNameUpper($table);
        $fileLines = file($oldFilePath, FILE_IGNORE_NEW_LINES);

        $endLineNumberDefinePermissions = $this->customRoutesFileEndLine($fileLines, '    /** end const permissions */');
        foreach (array_reverse(static::LIST_PERMISSIONS) as $permission) {
            $permissionUpper = strtoupper($permission);
            array_splice($fileLines, $endLineNumberDefinePermissions, 0, "\tconst $modelNameUpper".'_'."$permissionUpper = '$modelNameLower.$permission';");
        }

        $endLineNumberAllPermissions = $this->customRoutesFileEndLine($fileLines, '        ]; // all permissions');
        foreach (array_reverse(static::LIST_PERMISSIONS) as $permission) {
            $permissionUpper = strtoupper($permission);
            array_splice($fileLines, $endLineNumberAllPermissions, 0, "\t\t\tstatic::$modelNameUpper".'_'."$permissionUpper,");
        }
        $newFileContent = implode(PHP_EOL, $fileLines);
        file_put_contents($path, $newFileContent);
    }

    /**
     * @param $modelName
     * @param $table
     * @return void
     */
    public function addRoute($modelName, $table)
    {
        $resource_name = str_replace('_', '-', $table);
        $modelNameUpper = Helpers::generateModelNameUpper($table);
        $controllerName = $modelName.'Controller';
        $routeFileName = config('app.domain') === 'frontend' ? self::ROUTE_APP : self::ROUTE_ADMIN;
        $path = "routes/$routeFileName.php";

        $oldFilePath = base_path($path);

        // insert the given code before the file's last line
        $fileLines = file($oldFilePath, FILE_IGNORE_NEW_LINES);

        $endLineNumber = $this->customRoutesFileEndLine($fileLines) - 1;
        array_splice($fileLines, $endLineNumber, 0, "\t\t});\n");
        array_splice($fileLines, $endLineNumber, 0, "\t\t\tRoute::delete('/{id}', '$controllerName@destroy')->middleware('can:'.Permission::".$modelNameUpper."_DELETE);");
        array_splice($fileLines, $endLineNumber, 0, "\t\t\tRoute::put('/{id}', '$controllerName@update')->middleware('can:'.Permission::".$modelNameUpper."_EDIT);");
        array_splice($fileLines, $endLineNumber, 0, "\t\t\tRoute::get('/{id}', '$controllerName@show')->middleware('can:'.Permission::".$modelNameUpper."_VIEW);");
        array_splice($fileLines, $endLineNumber, 0, "\t\t\tRoute::post('/', '$controllerName@store')->middleware('can:'.Permission::".$modelNameUpper."_CREATE);");
        array_splice($fileLines, $endLineNumber, 0, "\t\t\tRoute::get('/', '$controllerName@index')->middleware('can:'.Permission::".$modelNameUpper."_LIST);");
        array_splice($fileLines, $endLineNumber, 0, "\t\tRoute::prefix('$resource_name')->group(function() {");

        $newFileContent = implode(PHP_EOL, $fileLines);
        file_put_contents($path, $newFileContent);
    }

    /**
     * @param $fileLines
     * @param null $needle
     * @return int|string|void|null
     */
    public function customRoutesFileEndLine($fileLines, $needle = null)
    {
        if ($needle) {
            $endLineNumber = array_search($needle, $fileLines);
        } else {
            // in case the last line has not been modified at all
            $endLineNumber = array_search('}); // end routes', $fileLines);
        }

        if ($endLineNumber) {
            return $endLineNumber;
        }

        // otherwise, in case the last line HAS been modified
        // return the last line that has an ending in it
        $possibleEndLines = array_filter($fileLines, function ($k) {
            return strpos($k, '});') === 0;
        });

        if ($possibleEndLines) {
            end($possibleEndLines);
            return key($possibleEndLines);
        }
    }
}
