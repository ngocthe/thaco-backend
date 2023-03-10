<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToMrpWeekDefinitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mrp_week_definitions', function (Blueprint $table) {
            $table->index([
                'year',
                'month_no',
                'week_no'
            ]);
            $table->index([
                'date'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mrp_week_definitions', function (Blueprint $table) {
            $table->dropIndex([
                'year',
                'month_no',
                'week_no'
            ]);
            $table->dropIndex([
                'date'
            ]);
        });
    }
}
