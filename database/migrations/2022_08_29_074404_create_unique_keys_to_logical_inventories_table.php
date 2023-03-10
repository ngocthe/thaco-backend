<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniqueKeysToLogicalInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logical_inventories', function (Blueprint $table) {
            $table->unique([
                'production_date',
                'part_code',
                'part_color_code',
                'plant_code'
            ], 'logical_inventories_unique');
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
        Schema::table('logical_inventories', function (Blueprint $table) {
            $table->dropUnique('logical_inventories_unique');
        });
    }
}
