<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueToVietnamSourceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vietnam_source_logs', function (Blueprint $table) {
            $table->unique([
                'contract_code',
                'part_code',
                'part_color_code',
                'box_type_code',
                'plant_code'
            ], 'vietnam_source_logs_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vietnam_source_logs', function (Blueprint $table) {
            $table->dropUnique('vietnam_source_logs_unique');
        });
    }
}
