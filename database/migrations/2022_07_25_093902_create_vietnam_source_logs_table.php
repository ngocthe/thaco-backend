<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVietnamSourceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vietnam_source_logs', function (Blueprint $table) {
            $table->id();
            $table->string("contract_code", 9);
			$table->string("invoice_code", 10)->nullable(true);
			$table->string("bill_of_lading_code", 13)->nullable(true);
			$table->string("container_code", 11);
			$table->string("case_code", 9)->nullable(true);
			$table->string("part_code", 10);
			$table->string("part_color_code", 2);
			$table->string("box_type_code", 5);
			$table->integer("box_quantity");
			$table->integer("part_quantity");
			$table->string("unit", 6);
			$table->string("supplier_code", 8);
			$table->date("delivery_date");
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
        Schema::dropIfExists('vietnam_source_logs');
    }
}
