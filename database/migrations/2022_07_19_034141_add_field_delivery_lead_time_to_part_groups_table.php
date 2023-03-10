<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldDeliveryLeadTimeToPartGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('part_groups', function (Blueprint $table) {
            $table->smallInteger('delivery_lead_time')->after('ordering_cycle')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('part_groups', function (Blueprint $table) {
            $table->dropColumn('delivery_lead_time');
        });
    }
}
