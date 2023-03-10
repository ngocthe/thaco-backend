<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFulltextSearchToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'box_types', 'ecns', 'mscs', 'part_colors', 'part_groups', 'parts', 'plants', 'suppliers',
            'vehicle_colors', 'warehouses', 'warehouse_locations'
        ];
        foreach ($tables as $table) {
            DB::statement('ALTER TABLE '.$table.' ADD FULLTEXT `search_code` (`code`)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
