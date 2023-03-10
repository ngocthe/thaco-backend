<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->string("msc_code", 10);
			$table->foreign("msc_code")->references('code')->on('mscs')->onDelete('cascade');
			$table->string("shop_code", 3);
			$table->string("part_code", 10);
			$table->foreign("part_code")->references('code')->on('parts')->onDelete('cascade');
			$table->string("part_color_code", 2);
			$table->foreign("part_color_code")->references('code')->on('part_colors')->onDelete('cascade');
			$table->smallInteger("quantity")->nullable(true);
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
        Schema::dropIfExists('boms');
    }
}
