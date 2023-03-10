<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShortagePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shortage_parts', function (Blueprint $table) {
            $table->id();
            $table->date("plan_date");
			$table->string("part_code", 10);
			$table->string("part_color_code", 2);
			$table->integer("quantity");
			$table->unsignedInteger("import_id");
			$table->string("plant_code", 5);
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
        Schema::dropIfExists('shortage_parts');
    }
}
