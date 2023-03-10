<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStatusIntoMrpOrderCalendar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('mrp_order_calendars', function (Blueprint $table) {
           $table->tinyInteger('status')->default(\App\Constants\MRP::MRP_ORDER_CALENDAR_STATUS_WAIT)->comment('1: Wait, 2: Done');
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
            $table->dropColumn('status');
        });
    }
}
