<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAndRemoveFieldContainerReceived extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->date('container_received')->nullable()->after('supplier_code');
        });

        Schema::table('in_transit_inventory_logs', function (Blueprint $table) {
            $table->dropColumn('container_received');
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
            $table->date('container_received')->nullable()->after('eta');
        });

        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->dropColumn('container_received');
        });
    }
}
