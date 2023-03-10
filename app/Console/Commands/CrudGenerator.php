<?php

namespace App\Console\Commands;

use App\Generator\JsonParser;
use App\Generator\JsonToController;
use App\Generator\JsonToMigration;
use App\Generator\JsonToModel;
use App\Generator\JsonToRequest;
use App\Generator\JsonToService;
use App\Generator\JsonToTransformer;
use Exception;
use Illuminate\Console\Command;

class CrudGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generator {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CRUD operations Api from JSON file.';

    /**
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->info("Parsing json File...");
        $json = (new JsonParser($this->argument('file')))->parse();

        $this->info("Creating migrations...");
        new JsonToMigration($json);
        $this->info("Migrations created!");

        $this->info("Creating models...");
        new JsonToModel($json);
        $this->info("Models created!");

        $this->info("Creating Requests...");
        new JsonToRequest($json);
        $this->info("Requests created!");

        $this->info("Creating Service...");
        new JsonToService($json);
        $this->info("Service created!");

        $this->info("Creating Transformer...");
        new JsonToTransformer($json);
        $this->info("Transformer created!");

        $this->info("Creating Controller...");
        new JsonToController($json);
        $this->info("Controller created!");
    }
}
