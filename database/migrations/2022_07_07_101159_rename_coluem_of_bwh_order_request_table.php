<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColuemOfBwhOrderRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_order_requests', function (Blueprint $table) {
            $table->renameColumn('warehouse_code', 'bonded_warehouse_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bwh_order_requests', function (Blueprint $table) {
            $table->renameColumn('bonded_warehouse_code', 'warehouse_code');
        });
    }
}
