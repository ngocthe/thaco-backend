<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VietnamSourceLog extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contract_code',
		'invoice_code',
		'bill_of_lading_code',
		'container_code',
		'case_code',
		'part_code',
		'part_color_code',
		'box_type_code',
		'box_quantity',
		'part_quantity',
		'unit',
		'supplier_code',
		'delivery_date',
		'plant_code'
    ];
}
