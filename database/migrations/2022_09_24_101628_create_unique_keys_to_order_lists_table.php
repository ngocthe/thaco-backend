<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniqueKeysToOrderListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_lists', function (Blueprint $table) {
            $table->unique([
                'contract_code',
                'eta',
                'part_code',
                'part_color_code',
                'plant_code'
            ], 'order_lists_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_lists', function (Blueprint $table) {
            $table->dropUnique('order_lists_unique');
        });
    }
}
