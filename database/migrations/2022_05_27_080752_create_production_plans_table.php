<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->date("plan_date");
			$table->string("msc_code", 10);
			$table->foreign("msc_code")->references('code')->on('mscs')->onDelete('cascade');
			$table->string("vehicle_color_code", 4)->nullable(true);
			$table->foreign("vehicle_color_code")->references('code')->on('vehicle_colors')->onDelete('cascade');
			$table->integer("volume");
			$table->string("input_file_name", 255);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_plans');
    }
}
