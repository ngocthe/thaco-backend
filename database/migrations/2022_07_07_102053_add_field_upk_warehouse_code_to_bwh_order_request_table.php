<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldUpkWarehouseCodeToBwhOrderRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_order_requests', function (Blueprint $table) {
            $table->string("upk_warehouse_code", 8)->nullable()->after('bonded_warehouse_code');
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
            $table->dropColumn('upk_warehouse_code');
        });
    }
}
