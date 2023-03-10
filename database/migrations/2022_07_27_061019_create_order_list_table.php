<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_list', function (Blueprint $table) {
            $table->id();
            $table->string("status", 100);
			$table->string("contract_code", 10);
			$table->date("eta")->nullable(true);
			$table->string("part_code", 10);
			$table->string("part_color_code", 2);
			$table->string("part_group", 2);
			$table->unsignedInteger("actual_quantity");
			$table->string("supplier_code", 5);
			$table->unsignedInteger("import_id")->nullable(true);
			$table->unsignedInteger("moq")->nullable(true);
			$table->unsignedInteger("mrp_quantity")->nullable(true);
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
        Schema::dropIfExists('order_list');
    }
}
