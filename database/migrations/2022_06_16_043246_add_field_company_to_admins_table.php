<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldCompanyToAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('company')->after('email')->nullable();
            $table->string('email')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->unsignedInteger('created_by')->after('remember_token')->nullable();
            $table->unsignedInteger('updated_by')->after('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
}
