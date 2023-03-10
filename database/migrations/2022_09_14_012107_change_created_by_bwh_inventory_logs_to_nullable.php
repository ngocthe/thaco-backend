<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCreatedByBwhInventoryLogsToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->change();
            $table->unsignedInteger('updated_by')->nullable()->change();
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
            $table->unsignedInteger('created_by')->nullable(false)->change();
            $table->unsignedInteger('updated_by')->nullable(false)->change();
        });
    }
}
