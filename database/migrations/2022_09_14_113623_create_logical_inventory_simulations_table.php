<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogicalInventorySimulationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logical_inventory_simulations', function (Blueprint $table) {
            $table->id();
            $table->date("production_date");
            $table->string("part_code", 10);
            $table->string("part_color_code", 2);
            $table->integer("quantity");
            $table->string("plant_code", 5);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['production_date', 'part_code', 'part_color_code', 'plant_code'], 'logical_inventory_simulations_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logical_inventory_simulations');
    }
}
