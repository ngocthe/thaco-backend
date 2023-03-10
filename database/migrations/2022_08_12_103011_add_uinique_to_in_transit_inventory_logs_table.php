<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUiniqueToInTransitInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('in_transit_inventory_logs', function (Blueprint $table) {
            $table->unique([
                'contract_code',
                'invoice_code',
                'bill_of_lading_code',
                'container_code',
                'case_code',
                'part_code',
                'part_color_code',
                'box_type_code',
                'plant_code'
            ], 'in_transit_inventory_logs_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('in_transit_inventory_logs', function (Blueprint $table) {
            $table->dropUnique('in_transit_inventory_logs_unique');
        });
    }
}
