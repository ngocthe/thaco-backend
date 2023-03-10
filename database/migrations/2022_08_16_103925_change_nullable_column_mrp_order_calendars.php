<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNullableColumnMrpOrderCalendars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mrp_order_calendars', function (Blueprint $table) {
            $table->string("buffer_span_from", 10)->nullable()->change();
            $table->string("buffer_span_to", 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mrp_order_calendars', function (Blueprint $table) {
            $table->string("target_plan_to", 10)->change();
            $table->string("buffer_span_from", 10)->change();
        });
    }
}
