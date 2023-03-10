<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldDefectIdToUpkwhInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upkwh_inventory_logs', function (Blueprint $table) {
            $table->string('defect_id', 2)->after('shipped_date')->nullable();
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
            $table->dropColumn('defect_id');
        });
    }
}
