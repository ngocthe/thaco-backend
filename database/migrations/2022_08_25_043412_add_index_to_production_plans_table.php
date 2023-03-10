<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToProductionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production_plans', function (Blueprint $table) {
            $table->index([
                'import_id',
                'plan_date',
                'msc_code',
                'vehicle_color_code'
            ], 'production_plan_index_1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_plans', function (Blueprint $table) {
            $table->dropIndex('production_plan_index_1');
        });
    }
}
