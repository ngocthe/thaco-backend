<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldWarehouseCodeToUpkwhInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upkwh_inventory_logs', function (Blueprint $table) {
            $table->string("warehouse_code", 8)->nullable()->after('warehouse_location_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upkwh_inventory_logs', function (Blueprint $table) {
            $table->dropColumn('warehouse_code');
        });
    }
}
