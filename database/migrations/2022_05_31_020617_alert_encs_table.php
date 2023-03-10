<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertEncsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecns', function (Blueprint $table) {
            $table->renameColumn('page', 'page_number');
            $table->renameColumn('line', 'line_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecns', function (Blueprint $table) {
            $table->renameColumn('page_number', 'page');
            $table->renameColumn('line_number', 'line');
        });
    }
}
