<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCreatedByUpkwhInventoryLogsToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upkwh_inventory_logs', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->change();
            $table->unsignedInteger('updated_by')->nullable()->change();
        });
        Schema::table('warehouse_inventory_summaries', function (Blueprint $table) {
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
        Schema::table('upkwh_inventory_logs', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable(false)->change();
            $table->unsignedInteger('updated_by')->nullable(false)->change();
        });
        Schema::table('warehouse_inventory_summaries', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable(false)->change();
            $table->unsignedInteger('updated_by')->nullable(false)->change();
        });
    }
}
