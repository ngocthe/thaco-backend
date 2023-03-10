<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement
 *
 * @property int $id
 * @property string $part_code
 * @property string $part_color_code
 * @property int|null $minimum_order_quantity
 * @property int|null $standard_box_quantity
 * @property int|null $part_quantity
 * @property string $unit
 * @property string $supplier_code
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @method static Builder|Procurement newModelQuery()
 * @method static Builder|Procurement newQuery()
 * @method static \Illuminate\Database\Query\Builder|Procurement onlyTrashed()
 * @method static Builder|Procurement query()
 * @method static Builder|Procurement whereContractCode($value)
 * @method static Builder|Procurement whereCreatedAt($value)
 * @method static Builder|Procurement whereCreatedBy($value)
 * @method static Builder|Procurement whereDeletedAt($value)
 * @method static Builder|Procurement whereId($value)
 * @method static Builder|Procurement whereMinimumOrderQuantity($value)
 * @method static Builder|Procurement wherePartCode($value)
 * @method static Builder|Procurement wherePartColorCode($value)
 * @method static Builder|Procurement wherePartQuantity($value)
 * @method static Builder|Procurement wherePlantCode($value)
 * @method static Builder|Procurement whereStandardBoxQuantity($value)
 * @method static Builder|Procurement whereSupplierCode($value)
 * @method static Builder|Procurement whereUnit($value)
 * @method static Builder|Procurement whereUpdatedAt($value)
 * @method static Builder|Procurement whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|Procurement withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Procurement withoutTrashed()
 * @mixin Eloquent
 */
class Procurement extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_code',
        'part_color_code',
        'minimum_order_quantity',
        'standard_box_quantity',
        'part_quantity',
        'unit',
        'supplier_code',
        'plant_code'
    ];
}
