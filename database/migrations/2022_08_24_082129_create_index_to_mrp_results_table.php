<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexToMrpResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mrp_results', function (Blueprint $table) {
            $table->index([
                'import_id',
                'plant_code',
                'production_date',
                'msc_code',
                'vehicle_color_code',
                'part_code',
                'part_color_code'
            ], 'mrp_results_index_1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mrp_results', function (Blueprint $table) {
            $table->dropIndex('mrp_results_index_1');
        });
    }
}
