<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_colors', function (Blueprint $table) {
            $table->id();
            $table->string("code", 4)->index();
			$table->string("type", 3);
			$table->string("name", 255);
			$table->string("ecn_in", 10);
			$table->foreign("ecn_in")->references('code')->on('ecns')->onDelete('cascade');
			$table->string("ecn_out", 10)->nullable(true);
			$table->foreign("ecn_out")->references('code')->on('ecns')->onDelete('cascade');
			$table->string("plant_code", 5);
			$table->foreign("plant_code")->references('code')->on('plants')->onDelete('cascade');
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
        Schema::dropIfExists('vehicle_colors');
    }
}
