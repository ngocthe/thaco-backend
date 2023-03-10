<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogicalInventoryMscAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logical_inventory_msc_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string("msc_code", 7);
			$table->integer("adjustment_quantity")->default(0);
			$table->string("vehicle_color_code", 4);
			$table->date("production_date");
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
        Schema::dropIfExists('logical_inventory_msc_adjustments');
    }
}
