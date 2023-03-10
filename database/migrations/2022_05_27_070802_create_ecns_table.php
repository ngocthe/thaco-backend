<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecns', function (Blueprint $table) {
            $table->id();
            $table->string("code", 10)->index();
			$table->mediumInteger("page");
			$table->mediumInteger("line");
			$table->string("description", 255);
			$table->string("mandatory_level", 1)->nullable(true);
			$table->string("production_interchangeability", 1)->nullable(true);
			$table->string("service_interchangeability", 1)->nullable(true);
			$table->string("released_party", 5)->nullable(true);
			$table->date("released_date")->nullable(true);
			$table->date("planned_line_off_date")->nullable(true);
			$table->date("actual_line_off_date")->nullable(true);
			$table->date("planned_packing_date")->nullable(true);
			$table->date("actual_packing_date")->nullable(true);
			$table->string("vin", 17)->nullable(true);
			$table->string("complete_knockdown", 13)->nullable(true);
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
        Schema::dropIfExists('ecns');
    }
}
