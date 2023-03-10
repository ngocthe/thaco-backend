<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCoulumToUpkwhInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upkwh_inventory_logs', function (Blueprint $table) {
            $table->renameColumn('received_box_quantity', 'box_quantity');
            $table->renameColumn('parts_quantity', 'part_quantity');
            $table->dropForeign(['warehouse_location_code']);
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
            //
        });
    }
}
