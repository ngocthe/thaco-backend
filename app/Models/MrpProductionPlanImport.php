<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MrpProductionPlanImport extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasFactory;

    const STATUS_NOT_RUN = 0;
    const STATUS_CHECKED_SHORTAGE = 1;
    const STATUS_RAN_MRP = 2;
    const STATUS_CAN_RUN_ORDER = 3;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_path',
		'original_file_name',
		'mrp_or_status',
        'mrp_or_progress',
        'mrp_or_result'
    ];
}
