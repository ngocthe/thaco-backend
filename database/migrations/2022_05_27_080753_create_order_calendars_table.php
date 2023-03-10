<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_calendars', function (Blueprint $table) {
            $table->id();
            $table->string("contract_code", 10);
			$table->string("part_group", 2);
			$table->foreign("part_group")->references('code')->on('part_groups')->onDelete('cascade');
			$table->date("etd");
			$table->date("eta");
			$table->mediumInteger("lead_time");
			$table->tinyInteger("ordering_cycle");
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
        Schema::dropIfExists('order_calendars');
    }
}
