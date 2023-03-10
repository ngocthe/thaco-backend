<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToShortagePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shortage_parts', function (Blueprint $table) {
            $table->index([
                'part_code',
                'part_color_code'
            ], 'shortage_part_index_1');
        });

        Schema::table('shortage_parts', function (Blueprint $table) {
            $table->index([
                'import_id',
                'plan_date',
                'part_code',
                'part_color_code'
            ], 'shortage_part_index_2');
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
            $table->dropIndex('shortage_part_index_1');
            $table->dropIndex('shortage_part_index_2');
        });
    }
}
