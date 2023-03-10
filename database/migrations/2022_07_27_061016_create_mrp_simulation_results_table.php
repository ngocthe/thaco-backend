<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMrpSimulationResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mrp_simulation_results', function (Blueprint $table) {
            $table->id();
            $table->date("plan_date");
			$table->string("msc_code", 7);
			$table->string("vehicle_color_code", 4);
			$table->integer("production_volume");
			$table->string("part_code", 10);
			$table->string("part_color_code", 2);
			$table->integer("part_requirement_quantity");
			$table->unsignedInteger("import_id");
			$table->string("plant_code", 5);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mrp_simulation_results');
    }
}
