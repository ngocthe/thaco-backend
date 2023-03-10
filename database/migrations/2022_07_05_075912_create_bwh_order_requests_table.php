<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBwhOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bwh_order_requests', function (Blueprint $table) {
            $table->id();
            $table->string("contract_code", 9);
			$table->string("invoice_code", 10);
			$table->string("bill_of_lading_no", 13);
			$table->string("container_code", 11);
			$table->string("case_code", 9);
			$table->string("part_code", 10);
			$table->foreign("part_code")->references('code')->on('parts')->onDelete('cascade');
			$table->string("part_color_code", 2);
			$table->foreign("part_color_code")->references('code')->on('part_colors')->onDelete('cascade');
			$table->string("box_type_code", 5);
			$table->foreign("box_type_code")->references('code')->on('box_types')->onDelete('cascade');
			$table->integer("box_quantity")->nullable(true);
			$table->integer("part_quantity")->nullable(true);
			$table->string("warehouse_code", 8)->nullable();
			$table->foreign("warehouse_code")->references('code')->on('warehouses')->onDelete('cascade');
			$table->string("warehouse_location_code", 8)->nullable();
			$table->foreign("warehouse_location_code")->references('code')->on('warehouse_locations')->onDelete('cascade');
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
        Schema::dropIfExists('bwh_order_requests');
    }
}
