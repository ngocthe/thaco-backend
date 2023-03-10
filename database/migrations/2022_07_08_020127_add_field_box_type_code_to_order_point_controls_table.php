<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldBoxTypeCodeToOrderPointControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_point_controls', function (Blueprint $table) {
            $table->string("box_type_code", 5)->nullable()->after('part_color_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_point_controls', function (Blueprint $table) {
            $table->dropColumn('box_type_code');
        });
    }
}
