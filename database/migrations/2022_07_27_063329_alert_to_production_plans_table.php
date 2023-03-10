<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertToProductionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production_plans', function (Blueprint $table) {
            $table->dropColumn('input_file_name');
            $table->unsignedInteger('import_id')->nullable()->after('volume');
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
            $table->dropColumn('import_id');
        });
    }
}
