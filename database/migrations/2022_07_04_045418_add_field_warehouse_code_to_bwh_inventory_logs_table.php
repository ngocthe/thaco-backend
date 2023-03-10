<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldWarehouseCodeToBwhInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->string("warehouse_code", 8)->nullable()->after('warehouse_location_code');
            $table->foreign("warehouse_code")->references('code')->on('warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->dropForeign(['warehouse_code']);
            $table->dropColumn('warehouse_code');
        });
    }
}
