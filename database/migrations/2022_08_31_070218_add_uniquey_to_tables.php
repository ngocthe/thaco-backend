<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueyToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shortage_parts', function (Blueprint $table) {
            $table->unique([
                'plan_date',
                'part_code',
                'part_color_code',
                'import_id',
                'plant_code'
            ], 'shortage_parts_unique_keys');
        });

        Schema::table('mrp_results', function (Blueprint $table) {
            $table->unique([
                'production_date',
                'msc_code',
                'vehicle_color_code',
                'part_code',
                'part_color_code',
                'import_id',
                'plant_code'
            ], 'mrp_results_unique_keys');
        });

        Schema::table('mrp_simulation_results', function (Blueprint $table) {
            $table->unique([
                'plan_date',
                'msc_code',
                'vehicle_color_code',
                'part_code',
                'part_color_code',
                'import_id',
                'plant_code'
            ], 'mrp_simulation_results_unique_keys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shortage_parts', function (Blueprint $table) {
            $table->dropUnique('shortage_parts_unique_keys');
        });
        Schema::table('mrp_results', function (Blueprint $table) {
            $table->dropUnique('mrp_results_unique_keys');
        });
        Schema::table('mrp_simulation_results', function (Blueprint $table) {
            $table->dropUnique('mrp_simulation_results_unique_keys');
        });
    }
}
