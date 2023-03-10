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
 * App\Models\OrderPointControl
 *
 * @property int $id
 * @property string $part_code
 * @property string $part_color_code
 * @property string $box_type_code
 * @property int $standard_stock
 * @property int $ordering_lot
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
 * @method static Builder|OrderPointControl newModelQuery()
 * @method static Builder|OrderPointControl newQuery()
 * @method static \Illuminate\Database\Query\Builder|OrderPointControl onlyTrashed()
 * @method static Builder|OrderPointControl query()
 * @method static Builder|OrderPointControl whereCreatedAt($value)
 * @method static Builder|OrderPointControl whereCreatedBy($value)
 * @method static Builder|OrderPointControl whereDeletedAt($value)
 * @method static Builder|OrderPointControl whereId($value)
 * @method static Builder|OrderPointControl wherePartCode($value)
 * @method static Builder|OrderPointControl wherePartColorCode($value)
 * @method static Builder|OrderPointControl wherePlantCode($value)
 * @method static Builder|OrderPointControl whereStandardStock($value)
 * @method static Builder|OrderPointControl whereUpdatedAt($value)
 * @method static Builder|OrderPointControl whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|OrderPointControl withTrashed()
 * @method static \Illuminate\Database\Query\Builder|OrderPointControl withoutTrashed()
 * @mixin Eloquent
 */
class OrderPointControl extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasRemark, WhereInMultiple, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_code',
		'part_color_code',
        'box_type_code',
		'standard_stock',
        'ordering_lot',
		'plant_code'
    ];
}
