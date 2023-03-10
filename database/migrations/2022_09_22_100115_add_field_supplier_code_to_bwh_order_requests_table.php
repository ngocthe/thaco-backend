<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldSupplierCodeToBwhOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_order_requests', function (Blueprint $table) {
            $table->string("supplier_code", 5)->after('case_code')->nullable();
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
            $table->dropColumn('supplier_code');
        });
    }
}
