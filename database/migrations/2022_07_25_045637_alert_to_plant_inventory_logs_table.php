<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertToPlantInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plant_inventory_logs', function (Blueprint $table) {
            $table->dropForeign(['box_type_code']);
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['warehouse_code']);

            $table->unsignedInteger('received_box_quantity')->after('quantity')->nullable();
            $table->string('defect_id', 2)->nullable()->after('warehouse_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plant_inventory_logs', function (Blueprint $table) {
            $table->dropColumn('received_box_quantity');
            $table->dropColumn('defect_id');
        });
    }
}
