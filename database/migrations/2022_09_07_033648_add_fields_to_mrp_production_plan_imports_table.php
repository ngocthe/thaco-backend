<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToMrpProductionPlanImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mrp_production_plan_imports', function (Blueprint $table) {
            $table->smallInteger('mrp_or_progress')->after('mrp_or_status')->default(0);
            $table->string('mrp_or_result', 255)->after('mrp_or_progress')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mrp_production_plan_imports', function (Blueprint $table) {
            $table->dropColumn('mrp_or_progress');
            $table->dropColumn('mrp_or_result');
        });
    }
}
