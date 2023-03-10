<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderCalendar extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contract_code',
		'part_group',
		'etd',
		'eta  ',
		'lead_time',
		'ordering_cycle'
    ];
}
