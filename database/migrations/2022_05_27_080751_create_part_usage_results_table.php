<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartUsageResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('part_usage_results', function (Blueprint $table) {
            $table->id();
            $table->date("used_date");
			$table->string("part_code", 10);
			$table->foreign("part_code")->references('code')->on('parts')->onDelete('cascade');
			$table->string("part_color_code", 2);
			$table->foreign("part_color_code")->references('code')->on('part_colors')->onDelete('cascade');
			$table->string("plant_code", 5);
			$table->foreign("plant_code")->references('code')->on('plants')->onDelete('cascade');
			$table->integer("quantity");
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
        Schema::dropIfExists('part_usage_results');
    }
}
