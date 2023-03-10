<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlantInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plant_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->string("part_code", 10);
			$table->foreign("part_code")->references('code')->on('parts')->onDelete('cascade');
			$table->string("part_color_code", 2);
			$table->foreign("part_color_code")->references('code')->on('part_colors')->onDelete('cascade');
			$table->string("box_type_code", 5);
			$table->foreign("box_type_code")->references('code')->on('box_types')->onDelete('cascade');
			$table->date("received_date");
			$table->unsignedInteger("quantity")->nullable();
			$table->string("unit", 6)->nullable();
			$table->string("warehouse_code", 8);
			$table->foreign("warehouse_code")->references('code')->on('warehouses')->onDelete('cascade');
			$table->string("plant_code", 5);
			$table->foreign("plant_code")->references('code')->on('plants')->onDelete('cascade');
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
        Schema::dropIfExists('plant_inventory_logs');
    }
}
