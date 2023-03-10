<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->json('receiver')->nullable()->after('forecast_by_month');
            $table->json('bcc')->nullable()->after('receiver');
            $table->json('cc')->nullable()->after('bcc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('receiver');
            $table->dropColumn('bcc');
            $table->dropColumn('cc');
        });
    }
}
