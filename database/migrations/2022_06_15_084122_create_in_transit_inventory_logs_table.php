<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInTransitInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('in_transit_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->string("contract_code", 9);
			$table->string("invoice_code", 10);
			$table->string("bill_of_lading_code", 13);
			$table->string("container_code", 11);
			$table->string("case_code", 9);
			$table->string("part_code", 10);
			$table->foreign("part_code")->references('code')->on('parts')->onDelete('cascade');
			$table->string("part_color_code", 2);
			$table->foreign("part_color_code")->references('code')->on('part_colors')->onDelete('cascade');
			$table->string("box_type_code", 5);
			$table->foreign("box_type_code")->references('code')->on('box_types')->onDelete('cascade');
			$table->integer("box_quantity")->nullable();
			$table->integer("part_quantity")->nullable();
			$table->string("unit", 6)->nullable();
			$table->string("supplier_code", 5);
			$table->foreign("supplier_code")->references('code')->on('suppliers')->onDelete('cascade');
			$table->date("etd")->nullable();
			$table->date("container_shipped")->nullable();
			$table->date("eta")->nullable();
			$table->date("container_received")->nullable();
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
        Schema::dropIfExists('in_transit_inventory_logs');
    }
}
