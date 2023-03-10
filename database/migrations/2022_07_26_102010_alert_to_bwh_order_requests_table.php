<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertToBwhOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_order_requests', function (Blueprint $table) {
            $table->renameColumn('bill_of_lading_no', 'bill_of_lading_code');
            $table->dropColumn('upk_warehouse_code');
            $table->renameColumn('bonded_warehouse_code', 'warehouse_code');
            $table->string('order_number')->nullable()->after('id');
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
            $table->renameColumn('bill_of_lading_code', 'bill_of_lading_no');
            $table->string('upk_warehouse_code')->nullable()->after('part_quantity');
            $table->renameColumn('warehouse_code', 'bonded_warehouse_code');
            $table->dropColumn('order_number');
        });
    }
}
