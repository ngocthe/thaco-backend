<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldDefectIdToBwhInventoryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->string('defect_id', 2)->after('requested')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->dropColumn('defect_id');
        });
    }
}
