<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefectInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defect_inventory', function (Blueprint $table) {
            $table->id();
            $table->string("modelable_type");
			$table->unsignedInteger("modelable_id");
			$table->unsignedInteger("box_id")->nullable();
			$table->string("defect_id", 2);
			$table->unsignedInteger("part_defect_quantity");
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
        Schema::dropIfExists('defect_inventory');
    }
}
