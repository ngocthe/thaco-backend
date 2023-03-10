<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldRequestedToBwhIntentoryLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bwh_inventory_logs', function (Blueprint $table) {
            $table->boolean('requested')->default(false)->after('plant_code');
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
            $table->dropColumn('requested');
        });
    }
}
