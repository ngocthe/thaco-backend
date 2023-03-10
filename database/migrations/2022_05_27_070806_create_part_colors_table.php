<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('part_colors', function (Blueprint $table) {
            $table->id();
            $table->string("code", 2)->index();
			$table->string("part_code", 10);
			$table->foreign("part_code")->references('code')->on('parts')->onDelete('cascade');
			$table->string("name", 255);
			$table->string("interior_code", 4)->nullable(true);
			$table->foreign("interior_code")->references('code')->on('vehicle_colors')->onDelete('cascade');
			$table->string("vehicle_color_code", 4)->nullable(true);
			$table->foreign("vehicle_color_code")->references('code')->on('vehicle_colors')->onDelete('cascade');
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
        Schema::dropIfExists('part_colors');
    }
}
