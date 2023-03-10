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
 * App\Models\BoxType
 *
 * @property int $id
 * @property string $code
 * @property string $part_code
 * @property string $description
 * @property int $weight
 * @property int $width
 * @property int $height
 * @property int $depth
 * @property int $quantity
 * @property string $unit
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
 * @method static Builder|BoxType newModelQuery()
 * @method static Builder|BoxType newQuery()
 * @method static \Illuminate\Database\Query\Builder|BoxType onlyTrashed()
 * @method static Builder|BoxType query()
 * @method static Builder|BoxType whereCode($value)
 * @method static Builder|BoxType whereCreatedAt($value)
 * @method static Builder|BoxType whereCreatedBy($value)
 * @method static Builder|BoxType whereDeletedAt($value)
 * @method static Builder|BoxType whereDepth($value)
 * @method static Builder|BoxType whereDescription($value)
 * @method static Builder|BoxType whereHeight($value)
 * @method static Builder|BoxType whereId($value)
 * @method static Builder|BoxType wherePartCode($value)
 * @method static Builder|BoxType wherePlantCode($value)
 * @method static Builder|BoxType whereQuantity($value)
 * @method static Builder|BoxType whereUnit($value)
 * @method static Builder|BoxType whereUpdatedAt($value)
 * @method static Builder|BoxType whereUpdatedBy($value)
 * @method static Builder|BoxType whereWeight($value)
 * @method static Builder|BoxType whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|BoxType withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BoxType withoutTrashed()
 * @mixin Eloquent
 */
class BoxType extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
		'part_code',
		'description',
		'weight',
		'width',
		'height',
		'depth',
		'quantity',
		'unit',
		'plant_code'
    ];
}
