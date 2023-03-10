<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMscsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mscs', function (Blueprint $table) {
            $table->id();
            $table->string("code", 7)->index();
			$table->string("description", 255);
			$table->string("interior_color", 255);
			$table->string("car_line", 255);
			$table->string("model_grade", 255);
			$table->string("body", 255);
			$table->string("engine", 255);
			$table->string("transmission", 255);
			$table->string("plant_code", 5);
			$table->foreign("plant_code")->references('code')->on('plants')->onDelete('cascade');
			$table->date("effective_date_in")->nullable(true);
			$table->date("effective_date_out")->nullable(true);
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
        Schema::dropIfExists('mscs');
    }
}
