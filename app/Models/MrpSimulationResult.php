<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MrpSimulationResult extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plan_date',
		'msc_code',
		'vehicle_color_code',
		'production_volume',
		'part_code',
		'part_color_code',
		'part_requirement_quantity',
		'import_id',
		'plant_code'
    ];
}
