<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldOrderingLotToOrderPointControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_point_controls', function (Blueprint $table) {
            $table->unsignedMediumInteger('ordering_lot')->default(0)->after('standard_stock');
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
            $table->dropColumn('ordering_lot');
        });
    }
}
