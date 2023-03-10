<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RechangeNameColumnLocationCodeBwhRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_order_requests', function (Blueprint $table) {
            $table->renameColumn('shelf_location_code', 'warehouse_location_code');
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
            $table->renameColumn('warehouse_location_code', 'shelf_location_code');
        });
    }
}
