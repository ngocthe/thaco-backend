<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogicalInventoryPartAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logical_inventory_part_adjustments', function (Blueprint $table) {
            $table->id();
            $table->date("adjustment_date");
			$table->string("part_code", 10);
			$table->string("part_color_code", 2);
			$table->integer("old_quantity")->default(0);
			$table->integer("new_quantity")->default(0);
			$table->integer("adjustment_quantity")->default(0);
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
        Schema::dropIfExists('logical_inventory_part_adjustments');
    }
}
