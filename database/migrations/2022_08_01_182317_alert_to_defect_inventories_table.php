<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertToDefectInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defect_inventories', function (Blueprint $table) {
            $table->unsignedInteger('part_defect_quantity')->nullable()->change();
            $table->string('defect_id',2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defect_inventories', function (Blueprint $table) {
            //
        });
    }
}
