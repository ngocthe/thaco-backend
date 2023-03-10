<?php

namespace App\Generator\Creators;

use App\Generator\Helpers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RequestCreator
{
    protected $requests;
    protected const ADMIN_FOLDER = 'Admin';
    protected const APP_FOLDER = 'App';

    public function __construct($requests)
    {
        $this->requests = $requests;
    }

    /**
     * @return void
     */
    public function create()
    {
        foreach ($this->requests as $table => $rules) {
            $this->createRequest($table, $rules);
            sleep(1);
        }
    }

    /**
     * @param $table
     * @param $rules
     * @return void
     */
    protected function createRequest($table, $rules)
    {
        $modelName = Helpers::generateModelName($table);
        $filename = $this->generateFileName($modelName);
        $domainFolder = $this->getDomainFolder();
        // Create request
        $stub = $this->createStub("Create$modelName", $modelName, $domainFolder, $rules);
        $path = $this->getPath("Create$filename", $domainFolder, $modelName);
        file_put_contents($path, $stub);
        // Update request
        $stub = $this->createStub("Update$modelName", $modelName, $domainFolder, $rules);
        $path = $this->getPath("Update$filename", $domainFolder, $modelName);
        file_put_contents($path, $stub);
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
        return sprintf('%sRequest.php', $modelName);
    }

    /**
     * @param $fileName
     * @param $modelName
     * @param $domainFolder
     * @param $rules
     * @return array|string|string[]
     */
    private function createStub($fileName, $modelName, $domainFolder, $rules)
    {
        $stub = $this->getStub();
        $stub = str_replace("{{fileName}}", $fileName, $stub);
        $stub = str_replace("{{modelName}}", $modelName, $stub);
        $stub = str_replace("{{domainFolder}}", $domainFolder, $stub);
        return str_replace("{{validationRules}}", $this->stringifyRules($rules), $stub);
    }

    /**
     * @param $rules
     * @return string
     */
    private function stringifyRules($rules): string
    {
        $rulesStr = '';
        foreach ($rules as $key => $rule) {
            $rulesStr .= "'" . $key . "' => '" . $rule . "', \n\t\t\t";
        }
        return rtrim($rulesStr, ",\n\t");
    }

    /**
     * @return false|string
     */
    private function getStub()
    {
        return file_get_contents(resource_path("stubs/request.stub"));
    }

    /**
     * @param $filename
     * @param $domainFolder
     * @param $modelName
     * @return string
     */
    private function getPath($filename, $domainFolder, $modelName): string
    {
        $dir = base_path("app/Http/Requests/$domainFolder/$modelName");
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }
        return "$dir/$filename";
    }
}
