<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMrpOrderCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mrp_order_calendars', function (Blueprint $table) {
            $table->id();
            $table->string("contract_code", 10);
			$table->string("part_group", 2);
			$table->date("etd");
			$table->date("eta");
			$table->string("target_plan_from", 10);
			$table->string("target_plan_to", 10);
			$table->string("buffer_span_from", 10);
			$table->string("buffer_span_to", 10);
			$table->string("order_span_from", 10)->nullable(true);
			$table->string("order_span_to", 10)->nullable(true);
			$table->date("mrp_or_run");
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
        Schema::dropIfExists('mrp_order_calendars');
    }
}
