<?php

namespace App\Generator\Creators;

use Illuminate\Support\Str;

class MigrationCreator
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
            $this->createMigration($table, $methods);

            // So migrations get created in order
            sleep(1);
        }
    }

    /**
     * @param $table
     * @param $methods
     * @return void
     */
    private function createMigration($table, $methods)
    {
        $filename = $this->generateFileName($table);
        $name = $this->generateName($table);
        $stub = $this->createStub($name, $table, $methods);
        $path = $this->getPath($filename);

        file_put_contents($path, $stub);
    }

    /**
     * @param $table
     * @return string
     */
    private function generateName($table): string
    {
        return Str::studly(
            sprintf("create_%s_table", strtolower($table))
        );
    }

    /**
     * @param $table
     * @return string
     */
    private function generateFileName($table): string
    {
        return sprintf('%s_create_%s_table.php', date('Y_m_d_His'), strtolower($table));
    }

    /**
     * @param $className
     * @param $tableName
     * @param $methods
     * @return array|string|string[]
     */
    private function createStub($className, $tableName, $methods)
    {
        $stub = $this->getStub();
        $stub = str_replace("{{migrationName}}", $className, $stub);
        $stub = str_replace("{{tableName}}", $tableName, $stub);
        return str_replace("{{methods}}", implode("\n\t\t\t", $methods), $stub);
    }

    /**
     * @return false|string
     */
    private function getStub()
    {
        return file_get_contents(resource_path("stubs/migration.stub"));
    }

    /**
     * @param $filename
     * @return string
     */
    private function getPath($filename): string
    {
        return base_path() . '/database/migrations/' . $filename;
    }
}
