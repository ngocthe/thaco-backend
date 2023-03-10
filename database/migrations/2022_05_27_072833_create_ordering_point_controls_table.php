<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderingPointControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_point_controls', function (Blueprint $table) {
            $table->id();
            $table->string("part_code", 10);
			$table->foreign("part_code")->references('code')->on('parts')->onDelete('cascade');
			$table->string("part_color_code", 2);
			$table->foreign("part_color_code")->references('code')->on('part_colors')->onDelete('cascade');
			$table->mediumInteger("standard_stock");
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
        Schema::dropIfExists('ordering_point_controls');
    }
}
