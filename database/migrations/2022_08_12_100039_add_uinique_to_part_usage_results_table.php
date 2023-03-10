<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUiniqueToPartUsageResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('part_usage_results', function (Blueprint $table) {
            $table->unique(['used_date', 'part_code', 'part_color_code', 'plant_code'], 'part_usage_results_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('part_usage_results', function (Blueprint $table) {
            $table->dropUnique('part_usage_results_unique');
        });
    }
}
