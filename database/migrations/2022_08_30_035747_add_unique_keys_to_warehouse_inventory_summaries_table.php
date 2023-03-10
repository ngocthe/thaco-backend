<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueKeysToWarehouseInventorySummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_inventory_summaries', function (Blueprint $table) {
            $table->unique([
                'part_code',
                'part_color_code',
                'warehouse_type',
                'warehouse_code',
                'plant_code'
            ], 'warehouse_inventory_summaries_unique_keys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_inventory_summaries', function (Blueprint $table) {
            $table->dropUnique('warehouse_inventory_summaries_unique_keys');
        });
    }
}
