<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertToBomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->dropForeign(['ecn_in']);
            $table->dropForeign(['ecn_out']);
            $table->dropForeign(['msc_code']);
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('boms', function (Blueprint $table) {
            //
        });
    }
}
