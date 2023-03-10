<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAllForeginKeysAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('box_types', function (Blueprint $table) {
            $table->dropForeign(['part_code']);
            $table->dropForeign(['plant_code']);
        });

        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->dropForeign(['box_type_code']);
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['supplier_code']);
            $table->dropForeign(['warehouse_code']);
            $table->dropForeign(['warehouse_location_code']);
        });

        Schema::table('bwh_order_requests', function (Blueprint $table) {
            $table->dropForeign(['box_type_code']);
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['warehouse_code']);
            $table->dropForeign(['warehouse_location_code']);
        });

        Schema::table('ecns', function (Blueprint $table) {
            $table->dropForeign(['plant_code']);
        });

        Schema::table('in_transit_inventory_logs', function (Blueprint $table) {
            $table->dropForeign(['box_type_code']);
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['supplier_code']);
        });

        Schema::table('order_point_controls', function (Blueprint $table) {
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
        });

        Schema::table('part_colors', function (Blueprint $table) {
            $table->dropForeign(['ecn_in']);
            $table->dropForeign(['ecn_out']);
            $table->dropForeign(['interior_code']);
            $table->dropForeign(['part_code']);
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['vehicle_color_code']);
        });

        Schema::table('part_usage_results', function (Blueprint $table) {
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->dropForeign(['ecn_in']);
            $table->dropForeign(['ecn_out']);
            $table->dropForeign(['group']);
            $table->dropForeign(['plant_code']);
        });

        Schema::table('procurements', function (Blueprint $table) {
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['supplier_code']);
        });

        Schema::table('production_plans', function (Blueprint $table) {
            $table->dropForeign(['msc_code']);
            $table->dropForeign(['vehicle_color_code']);
        });

        Schema::table('upkwh_inventory_logs', function (Blueprint $table) {
            $table->dropForeign(['box_type_code']);
            $table->dropForeign(['part_code']);
            $table->dropForeign(['part_color_code']);
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['supplier_code']);
        });

        Schema::table('vehicle_colors', function (Blueprint $table) {
            $table->dropForeign(['ecn_in']);
            $table->dropForeign(['ecn_out']);
            $table->dropForeign(['plant_code']);
        });

        Schema::table('warehouse_inventory_summaries', function (Blueprint $table) {
            $table->dropForeign('wh_inventory_summaries_part_code_foreign');
            $table->dropForeign('wh_inventory_summaries_part_color_code_foreign');
            $table->dropForeign('wh_inventory_summaries_plant_code_foreign');
            $table->dropForeign('wh_inventory_summaries_warehouse_code_foreign');
        });

        Schema::table('warehouse_locations', function (Blueprint $table) {
            $table->dropForeign(['plant_code']);
            $table->dropForeign(['warehouse_code']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
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
        //
    }
}
